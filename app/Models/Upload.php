<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Database\Factories\UploadFactory;

class Upload extends BaseModel
{
    use HasFactory;
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'uploads';

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'processed' => 0,
        'total' => 0,
        'status' => self::STATUS_PENDING
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'filename',
        'filepath',
        'processed',
        'total',
        'status'
    ];

    /**
     * Status values
     */
    const STATUS_PENDING = 'pending';

    const STATUS_PROCESSING = 'processing';

    const STATUS_FAILED = 'failed';

    const STATUS_COMPLETED = 'completed';

    const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_PROCESSING,
        self::STATUS_FAILED,
        self::STATUS_COMPLETED
    ];

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return UploadFactory::new();
    }
}
