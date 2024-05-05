<?php

namespace Sicaboy\LaravelDashboard\Models;

use Illuminate\Database\Eloquent\Model;

class Dashboard extends Model
{
    protected $table = 'app_dashboard';

    public function sections()
    {
        return $this->hasMany(DashboardSection::class, 'dashboard_id', 'id');
    }

    public function searchFields()
    {
        return $this->hasMany(DashboardSearchField::class, 'dashboard_id', 'id');
    }
}
