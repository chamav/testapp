<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Storage;


/**
 * App\UserFile
 *
 * @property integer $id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string $name
 * @property string $hash
 * @property integer $size
 * @property integer $user_id
 * @property \Carbon\Carbon $deleted_at
 * @property-read \App\User $user
 * @method static \Illuminate\Database\Query\Builder|\App\UserFile whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\UserFile whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\UserFile whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\UserFile whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\UserFile whereHash($value)
 * @method static \Illuminate\Database\Query\Builder|\App\UserFile whereSize($value)
 * @method static \Illuminate\Database\Query\Builder|\App\UserFile whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\UserFile whereDeletedAt($value)
 * @mixin \Eloquent
 * @property boolean $actual
 * @property integer $type 0 - image 1 - other
 * @property string $processed Timestamp processed image
 * @method static \Illuminate\Database\Query\Builder|\App\UserFile whereActual($value)
 * @method static \Illuminate\Database\Query\Builder|\App\UserFile whereType($value)
 * @method static \Illuminate\Database\Query\Builder|\App\UserFile whereProcessed($value)
 * @property string $mime
 * @method static \Illuminate\Database\Query\Builder|\App\UserFile whereMime($value)
 * @property integer $item_id
 * @method static \Illuminate\Database\Query\Builder|\App\UserFile whereItemId($value)
 * @property-read \App\Item $present
 * @property-read mixed $uri
 * @property-read mixed $url
 * @property integer $thank_id
 * @property-read \App\Thank $thanks
 * @method static \Illuminate\Database\Query\Builder|\App\UserFile whereThankId($value)
 * @property integer $avatar
 * @property mixed $resolutions
 * @method static \Illuminate\Database\Query\Builder|\App\UserFile whereAvatar($value)
 * @method static \Illuminate\Database\Query\Builder|\App\UserFile whereResolutions($value)
 */
class UserFile extends Model
{
    use SoftDeletes;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'hash', 'size', 'user_id', 'created_at', 'type', 'mime', 'avatar'];

    const TYPE_IMAGE = 0; //Image document
    const TYPE_OTHER = 1; //Other document
    const TYPES = [
        0 => 'image',
        1 => 'other',
    ];
    protected $uri_f = null;

    /**
     * The storage format of the model's date columns.
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:sO';
    /**
     * Get the user that owns the file.
     */
    public function user()
    {
        return $this->belongsTo(AuthUser::class);
    }

    /**
     * Get URI on the server
     * return string
     *
     * @return string
     */
    public function getUrl()
    {
        if(is_null($this->uri_f)){
            $this->uri_f = Storage::url($this->created_at->setTimezone('UTC')->toDateString().DIRECTORY_SEPARATOR.$this->hash.'.'.pathinfo($this->name, PATHINFO_EXTENSION));
        }
        return $this->uri_f;
    }



    public function getUrlAttribute()
    {
        return $this->getUrl();
    }


    /**
     * Get file type.
     *
     * @param  string  $value
     * @return string
     */
    public function getTypeAttribute($value)
    {
        $t = self::TYPES;

        if(!isset($t[$value]))
        {
            return null;
        }
        return self::TYPES[$value];
    }
}
