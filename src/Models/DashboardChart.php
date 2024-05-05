<?php

namespace Sicaboy\LaravelDashboard\Models;

use Illuminate\Database\Eloquent\Model;

class DashboardChart extends Model
{
    protected $table = 'app_dashboard_chart';
    protected $casts = [
        'options' => 'array',
    ];

    public function series()
    {
        return $this->hasMany(DashboardChartSeries::class, 'chart_id', 'id');
    }
}
