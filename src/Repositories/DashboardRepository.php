<?php

namespace Sicaboy\LaravelDashboard\Repositories;

use Sicaboy\LaravelDashboard\Models\Dashboard;
use Sicaboy\LaravelDashboard\Services\QueryService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardRepository
{
    protected array $errors = [];
    protected array $params = [];
    /**
     * Model
     * @var Dashboard | null
     */
    protected ?Dashboard $dashboard = null;

    /**
     * DashboardRepository constructor.
     * @param QueryService $queryService
     */
    public function __construct(protected QueryService $queryService)
    {
    }

    /**
     * @param $id
     * @param $params
     * @return array
     * @throws \Exception
     */
    public function getDashboards($id, $params = null): array
    {
        $this->params = $params;
        $this->dashboard = Dashboard::findOrFail($id);
        $dashboardSections = $this->dashboard->sections()->orderBy('order', 'ASC')->get();
        $searchFields = $this->dashboard->searchFields()->orderBy('order', 'ASC')->get();
        $result = [
            'sections' => $this->params ? $this->getSections($dashboardSections) : [],
            'dashboard' => $this->dashboard->toArray(),
            'search_fields' => $searchFields,
        ];
        if (count($this->errors)) {
            return [
                'errors' => $this->errors
            ];
        }
        return $result;
    }

    /**
     * @param $dashboardSections
     * @return array
     */
    private function getSections($dashboardSections): array
    {
        $sections = [];
        foreach ($dashboardSections as $dashboardSection) {
            $sections[] = [
                'order' => intval($dashboardSection->order),
                'title' => $dashboardSection->title,
                'chart_groups' => $this->getChartGroups($dashboardSection->chartGroups)
            ];
        }
        return $sections;
    }

    /**
     * @param $dashboardChartGroups
     * @return array
     */
    private function getChartGroups($dashboardChartGroups): array
    {
        $group = [];
        foreach ($dashboardChartGroups as $dashboardChartGroup) {
            $group[] = [
                'id' => intval($dashboardChartGroup->id),
                'title' => $dashboardChartGroup->title,
                'description' => $dashboardChartGroup->description,
                'options' => $dashboardChartGroup->options,
                'charts' => $this->getCharts($dashboardChartGroup->charts),
                'tabs' => $this->getChartTabs($dashboardChartGroup->charts)
            ];
        }
        return $group;
    }


    /**
     * @param $charts
     * @return array
     */
    private function getCharts($charts): array
    {
        $output = [];
        foreach ($charts as $chart) {
            $rawSeries = $this->getChartSeries($chart->series);
            $arr = [
                'id' => intval($chart->id),
                'title' => $chart->title,
                'type' => $chart->type,
                'tab' => $chart->tab,
                'options' => $chart->options,
                'showif' => $chart->showif,
                'raw_series' => $rawSeries,
            ];
            $arr = array_merge($arr, $this->restructureSeriesArrayToChartJsFormat($rawSeries));
            // Handle Special Chart
            if ($chart->type == 'table') {
                $arr = array_merge($arr, $this->restructureSeriesArrayToTableFormat($rawSeries));
            }
            if ($chart->type == 'static-table') {
                $arr = array_merge($arr, $this->restructureSeriesArrayToStaticTableFormat($rawSeries));
            }
            $output[] = $arr;
        }
        return $output;
    }

    /**
     * @param $charts
     * @return array
     */
    private function getChartTabs($charts): array
    {
        $output = [];
        foreach ($charts as $chart) {
            $tabs = explode(',', $chart->tab);
            foreach ($tabs as $tab) {
                if (!empty($tab) && !in_array($tab, $output)) {
                    $output[] = $tab;
                }
            }
        }
        return $output;
    }

    /**
     * Re-structure from DB hierarchy to ChartJs format
     * @param $rawSeries
     * @return array
     */
    private function restructureSeriesArrayToChartJsFormat($rawSeries): array
    {
        $output = [];
        foreach ((array)$rawSeries as $seriesItem) {
            foreach ((array)$seriesItem as $key => $seriesFlat) {
                if ($key == 'title') {
                    $output['series'][] = data_get($seriesItem, $key);
                } elseif ($key == 'labels') {
                    if (empty($output['labels'])) {
                        $output['labels'] = data_get($seriesItem, $key);
                    }
                } elseif ($key == 'showif') {
                    // Skip
                } else {
                    $output[$key][] = data_get($seriesItem, $key);
                }
            }
        }
        return $output;
    }

    /**
     * Re-structure Series Array To Table Format
     * @param $rawSeries
     * @return mixed
     */
    private function restructureSeriesArrayToTableFormat($rawSeries)
    {
        $table = [];
        foreach ((array)$rawSeries as $seriesItem) {
            foreach ((array)$seriesItem as $columnName => $columnList) {
                if ($columnName == 'title' || $columnName == 'extra' || $columnName == 'showif') {
                    continue;
                }
                foreach ((array)$columnList as $rowNum => $text) {
                    if (!isset($table[$rowNum])) {
                        $table[$rowNum] = [];
                    }
                    $order = array_search($columnName, $seriesItem['extra']['order']);
                    if ($order !== false) {
                        $table[$rowNum][$order] = [
                            'columnName' => $columnName,
                            'text' => $text,
                            'click' => !empty($seriesItem['extra']['click'][$columnName]) ? $seriesItem['extra']['click'][$columnName] : null,
                            'figure' => !empty($seriesItem['extra']['figure']) ? in_array($columnName, $seriesItem['extra']['figure']) : null,
                        ];
                    }
                }
            }
        }
        $output['table'] = $table;
        return $output;
    }

    /**
     * Re-structure Series Array To Static Table Format
     * @param $rawSeries
     * @return mixed
     */
    private function restructureSeriesArrayToStaticTableFormat($rawSeries): mixed
    {
        $table = [];
        foreach ((array)$rawSeries as $rowNum => $seriesItem) {
            if (!isset($table[$rowNum])) {
                $table[$rowNum] = [];
            }
            $table[$rowNum][0] = [ // To make it first displayed
                'columnName' => 'title',
                'text' => $seriesItem['title'],
            ];
            foreach ((array)$seriesItem as $columnName => $columnList) {
                if ($columnName == 'title' || $columnName == 'extra' || $columnName == 'showif') {
                    continue;
                }
                $order = array_search($columnName, $seriesItem['extra']['order']);
                if ($order !== false) {
                    $table[$rowNum][$order + 1] = [ // To allow title in front
                        'columnName' => $columnName,
                        'text' => count($columnList) ? $columnList[0] : "",
                    ];
                }
            }
        }
        $output['table'] = $table;
        return $output;
    }

    /**
     * getChartSeries
     * @param $chartSeries
     * @return array
     * @throws \Exception
     */
    private function getChartSeries($chartSeries): array
    {
        $output = [];
        foreach ($chartSeries as $series) {
            $fieldsValues = []; // The fields need to be in the json output through running SQL for this specific chart
            $result = [];
            $fromDateCarbon = Carbon::parse(data_get($this->params, 'fromDate', $this->dashboard->from_date));
            $toDateCarbon = Carbon::parse(data_get($this->params, 'toDate', $this->dashboard->to_date));
            $fromDate = $fromDateCarbon->toDateString();
            $toDate = $toDateCarbon->toDateString();
            $queryTemplateKey = $series->query_template_key;
            $durationDays = $toDateCarbon->diffInDays($fromDateCarbon);

            // CAUTION: Day range period separator should be the same with front-end dashboardController.ts getLineChartUnit()
            if (!empty($series->extra['conditional_query_template']['weekly']) && $durationDays > 92 && $durationDays <= 365) {
                $queryTemplateKey = $series->extra['conditional_query_template']['weekly'];
            } elseif (!empty($series->extra['conditional_query_template']['monthly']) && $durationDays > 365) {
                $queryTemplateKey = $series->extra['conditional_query_template']['monthly'];
            }

            if ($queryTemplateKey) {
                $result = $this->queryService->runQuery($queryTemplateKey, array_merge(
                    (array)$this->params, // POST parameters
                    [
                        'dateRange' => [$fromDate, $toDate],
                        'fromDate' => $fromDate,
                        'toDate' => $toDate,
                    ],
                    (array)$series->variable // Forced parameters
                ));
            } elseif ($series->sql) {
                // Run SQL
                $result = DB::select($series->sql, [$fromDate, $toDate]);
            }
            foreach ($series->fields as $field) {
                $fieldsValuesLine = [];
                foreach ($result as $item) {
                    $fieldsValuesLine[] = object_get($item, $field->field, '');
                }
                $fieldsValues[$field->field] = $fieldsValuesLine;
            }
            $arr = [
                'title' => $series->title,
                'description' => $series->description,
                'showif' => $series->showif,
                'extra' => $series->extra,
            ];
            $output[] = array_merge($arr, $fieldsValues);
        }
        return $output;
    }
}
