<?php
/**
 * This code is licensed under AGPLv3 license or Afterlogic Software License
 * if commercial version of the product was purchased.
 * For full statements of the licenses see LICENSE-AFTERLOGIC and LICENSE-AGPL3 files.
 */

namespace Aurora\Modules\RecaptchaWebclientPlugin\Models;

use Aurora\System\Classes\Model;
use Aurora\Modules\Core\Models\User;

/**
 * @license https://www.gnu.org/licenses/agpl-3.0.html AGPL-3.0
 * @license https://afterlogic.com/products/common-licensing Afterlogic Software License
 * @copyright Copyright (c) 2023, Afterlogic Corp.
 * @property int    $Id
 * @property int|null $UserId
 * @property string $Email
 * @property string $IpAddress
 * @property int    $ErrorLoginsCount
 * @property int    $Time
 * @property \Illuminate\Support\Carbon|null $CreatedAt
 * @property \Illuminate\Support\Carbon|null $UpdatedAt
 */
class AuthFailure extends Model
{
    protected $table = 'recaptcha_auth_failures';
    protected $moduleName = 'RecaptchaWebclientPlugin';

    protected $foreignModel = User::class;
    protected $foreignModelIdColumn = 'UserId';

    protected $fillable = [
        'Id',
        'UserId',
        'Email',
        'IpAddress',
        'ErrorLoginsCount',
        'Time',
    ];
}
