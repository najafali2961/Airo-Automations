<?php

namespace App\Models;


use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Osiset\ShopifyApp\Contracts\ShopModel as IShopModel;
use Osiset\ShopifyApp\Traits\ShopModel;

class User extends Authenticatable implements IShopModel
{
    use Notifiable;
    use ShopModel;


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'plan_id',
        'shopify_freemium',
        'credits',
        'credits_used',
        'credits_reset_at',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'credits' => 'integer',
        'credits_used' => 'integer',
        'credits_reset_at' => 'datetime',
        'shopify_freemium' => 'boolean',
    ];

    /**
     * Get workflows
     */
    public function workflows()
    {
        return $this->hasMany(Workflow::class, 'shop_id');
    }

    /**
     * Get flows (New Automation Builder)
     */
    public function flows()
    {
        return $this->hasMany(Flow::class, 'shop_id');
    }

    /**
     * Get barcode printer settings
     */
    public function barcodePrinterSettings()
    {
        return $this->hasMany(BarcodePrinterSetting::class);
    }

    /**
     * Get label templates
     */
    public function labelTemplates()
    {
        return $this->hasMany(LabelTemplate::class);
    }

    /**
     * Get products
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get default label template
     */
    public function defaultLabelTemplate()
    {
        return $this->hasOne(LabelTemplate::class)->where('is_default', true);
    }

    /**
     * Check if user is on freemium plan
     */
    public function isFreemium(): bool
    {
        return $this->shopify_freemium == 1 || $this->plan_id === null;
    }

    /**
     * Check if user has active paid plan
     */
    public function hasPaidPlan(): bool
    {
        return !$this->isFreemium() && $this->plan_id !== null;
    }
}
