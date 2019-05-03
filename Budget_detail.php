<?php

/* Model for Budget_detail

namespace App\Models\Budgets;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

//use OwenIt\Auditing\Auditable;
//use OwenIt\Auditing\Contracts\Auditable as AuditableContract;


class Budget_detail extends Model //implements AuditableContract
{
    use SoftDeletes;
    //use Auditable;

    protected $table = 'budget_details';



    public function budget(){
        return $this->belongsTo('App\Models\Budgets\Budget', 'id_budget');
    }


    public function product(){
        return $this->belongsTo('App\Models\Products\Product', 'id_product');
    }

    public function budget_credit(){
        return $this->hasOne('App\Models\Budgets\Budget_credit', 'id_budget_detail');
    }
    
    public function budget_cash(){
        return $this->hasOne('App\Models\Budgets\Budget_cash', 'id_budget_detail');
    }
    
    public function budget_check(){
        return $this->hasOne('App\Models\Budgets\Budget_check', 'id_budget_detail');
    }
    
    public function budget_document(){
        return $this->hasOne('App\Models\Budgets\Budget_document', 'id_budget_detail');
    }
    
    public function budget_expense(){
        return $this->hasOne('App\Models\Budgets\Budget_expense', 'id_budget_detail');
    }
    
    public function budget_used(){
        return $this->hasOne('App\Models\Budgets\Budget_used', 'id_budget_detail');
    }
    
    public function budget_plan_payment(){
        return $this->hasOne('App\Models\Budgets\Plan_payment', 'id_budget_detail');
    }

    public function blue_cedules(){
        return $this->hasMany('App\Models\Budgets\Blue_cedule', 'id_budget_detail');
    }
    
    public function currency(){
        return $this->belongsTo('App\Models\Currency', 'id_currency');
    }


    protected $casts = [
        'data' => 'array'
    ];



}