<?php

namespace Sicaboy\LaravelDashboard\Models;

use Illuminate\Database\Eloquent\Model;

class DashboardSection extends Model
{
    protected $table = 'app_dashboard_section';

    public function chartGroups()
    {
        return $this->hasMany(DashboardChartGroup::class, 'section_id', 'id');
    }

    public function dashboard()
    {
        return $this->belongsTo(Dashboard::class);
    }
}
