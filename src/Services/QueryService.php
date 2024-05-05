<?php

namespace Sicaboy\LaravelDashboard\Services;

use Sicaboy\LaravelDashboard\Repositories\QueryRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class QueryService
{
    public function __construct(private readonly QueryRepository $queryRepository)
    {
    }

    /**
     * Generate the final SQL from template
     * @param $queryTemplateKey string Query template Key
     * @param $inputVariables array User input condition variables to meet the variables defined in `app_query_variables`
     * @return array [Final SQL, BindValues Array]
     * @throws \Exception
     */
    public function generateSqlQuery(string $queryTemplateKey, array $inputVariables): array
    {

        // Fetch the template
        $templateModel = $this->queryRepository->getTemplateByKey($queryTemplateKey);
        $bindValue = [];

        $pattern = '/##(?P<variable>[^#]*)##/';
        preg_match_all($pattern, $templateModel->sql, $matches);
        $variablesInQuery = $matches['variable'];
        $segmentSql = "";
        $finalSql = $templateModel->sql;
        foreach ($variablesInQuery as $variable) {
            $variableModel = $templateModel->variables()->where('variable', $variable)->first();
            // Check variable availability
            if (!$variableModel) {
                throw new \Exception("QueryTemplateId={$queryTemplateKey}: variable '{$variable}' is not defined in table `app_query_variables`");
            }
            // Pair the variable with query
            if (!isset($inputVariables[$variable]) || $inputVariables[$variable] === null) {
                if ($variableModel->required) {
                    throw new \Exception("QueryTemplateId={$queryTemplateKey}: '{$variable}' is required");
                } else {
                    $segmentSql = $variableModel->default;
                }
            } elseif ($variableModel->variable !== null) {
                switch (strtolower($variableModel->operator)) {
                    case 'db_field':
                        $segmentSql = $inputVariables[$variableModel->variable];
                        break;
                    case 'between':
                        $segmentSql = " $variableModel->field BETWEEN ? AND ? ";
                        $bindValue[] = $inputVariables[$variableModel->variable][0]; // Date start
                        $bindValue[] = $inputVariables[$variableModel->variable][1]; // Date end
                        break;
                    case 'in':
                        $inQuery = implode(',', array_fill(0, count($inputVariables[$variableModel->variable]), '?'));
                        $segmentSql = " $variableModel->field IN ( $inQuery ) ";
                        foreach ($inputVariables[$variableModel->variable] as $bindValuePart) {
                            $bindValue[] = $bindValuePart;
                        }
                        break;
                    default:
                        $segmentSql = " {$variableModel->field} {$variableModel->operator} ? ";
                        $bindValue[] = (string)$inputVariables[$variableModel->variable];
                        break;
                }
            }
            $pattern = "/##{$variable}##/";
            $finalSql = preg_replace($pattern, $segmentSql, $finalSql);
        }
        return [
            $finalSql,
            $bindValue
        ];
    }

    /**
     * Run query after assembling the template with input variables.
     * @param $queryTemplateKey string  Query template Key
     * @param $inputVariables array
     * @return mixed
     * @throws \Exception
     */
    public function runQuery(string $queryTemplateKey, array $inputVariables): mixed
    {
        list($sql, $variablesArr) = $this->generateSqlQuery($queryTemplateKey, $inputVariables);
        // Special variable placeholder replace
        foreach ($variablesArr as $k => $variable) {
            if ($variable == ':auth:') {
                $variablesArr[$k] = Auth::user()->id;
            }
        }
        $cacheKey = md5(serialize($sql) . serialize($variablesArr));
        if ($result = Cache::get($cacheKey)) {
            return $result;
        }
        $result = DB::select($sql, $variablesArr);
        Cache::put($cacheKey, $result, Carbon::now()->addMinutes(1));
        return $result;
    }

    /**
     * @param $queryTemplateKey
     * @param $inputVariables
     * @param bool $validateOnly
     * @return bool
     * @throws \Exception
     */
    public function generateReport($queryTemplateKey, $inputVariables, bool $validateOnly = false): bool
    {
        // Fetch the template
        $templateModel = $this->queryRepository->getTemplateByKey($queryTemplateKey);
        $result = $this->runQuery($queryTemplateKey, $inputVariables);
        if ($validateOnly) {
            return true;
        }
        Excel::create('report', function ($excel) use ($templateModel, $result) {
            $excel->sheet('sheet', function ($sheet) use ($templateModel, $result) {

                $selects = $templateModel->select;
                $tableHead = [];
                foreach ($selects as $select) {
                    if ($select->field) {
                        $tableHead[] = $select;
                    }
                }

                $line = [];
                foreach ($tableHead as $th) {
                    $line[] = $th->title;
                }
                $sheet->appendRow($line);

                foreach ($result as $row) {
                    $line = [];
                    foreach ($tableHead as $th) {
                        $field = $th->field;
                        $line[] = $row->$field;
                    }
                    $sheet->appendRow($line);
                }
            });
        })->download('csv');
        exit;
//        $variableModel = $templateModel->variables()->where('variable', $variable)->first();
    }
}
