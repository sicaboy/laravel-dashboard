<?php

namespace Sicaboy\LaravelDashboard\Models;

use Illuminate\Database\Eloquent\Model;

class QueryTemplate extends Model
{

    protected $table = 'app_query_templates';

    public function selects()
    {
        return $this->hasMany(QuerySelect::class, 'template_id', 'id');
    }

    public function variables()
    {
        return $this->hasMany(QueryVariable::class, 'template_id', 'id');
    }
}
