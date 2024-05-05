<?php

namespace Sicaboy\LaravelDashboard\Repositories;

use Sicaboy\LaravelDashboard\Models\QueryTemplate;

class QueryRepository
{

    /**
     * Get Templates Collection
     * @return mixed
     */
    public function getTemplateList(): mixed
    {
        return QueryTemplate::with('variables')->all();
    }

    public function getTemplateByKey($key)
    {
        return QueryTemplate::where('template_key', $key)->first();
    }

//    public function getTemplateById($id) {
//        return QueryTemplate::findOrFail($id);
//    }
}
