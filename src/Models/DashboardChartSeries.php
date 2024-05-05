<?php

namespace Sicaboy\LaravelDashboard\Models;

use Illuminate\Database\Eloquent\Model;

class DashboardChartSeries extends Model
{
    protected $table = 'app_dashboard_chart_series';
    protected $casts = [
        'variable' => 'array',
        'extra' => 'array',
        'options' => 'array',
    ];

    public function fields()
    {
        return $this->hasMany(DashboardChartField::class, 'chart_series_id', 'id');
    }
}
