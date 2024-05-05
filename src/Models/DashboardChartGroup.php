<?php

namespace Sicaboy\LaravelDashboard\Models;

use Illuminate\Database\Eloquent\Model;

class DashboardChartGroup extends Model
{
    protected $table = 'app_dashboard_chart_group';
    protected $casts = [
        'options' => 'array',
    ];

    public function charts()
    {
        return $this->hasMany(DashboardChart::class, 'chart_group_id', 'id');
    }
}
