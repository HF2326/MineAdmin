<?php

declare(strict_types=1);
/**
 * This file is part of MineAdmin.
 *
 * @link     https://www.mineadmin.com
 * @document https://doc.mineadmin.com
 * @contact  root@imoi.cn
 * @license  https://github.com/mineadmin/MineAdmin/blob/master/LICENSE
 */

namespace App\Model\Permission;

use App\Model\Enums\User\Status;
use App\Model\Enums\User\Type;
use Carbon\Carbon;
use Hyperf\Collection\Collection;
use Hyperf\Database\Model\Events\Creating;
use Hyperf\Database\Model\Relations\BelongsToMany;
use Hyperf\DbConnection\Model\Model;
use Mine\Kernel\Casbin\Rule\Rule;

/**
 * @property int $id 用户ID，主键
 * @property string $username 用户名
 * @property Type $user_type 用户类型：(100系统用户)
 * @property string $nickname 用户昵称
 * @property string $phone 手机
 * @property string $email 用户邮箱
 * @property string $avatar 用户头像
 * @property string $signed 个人签名
 * @property string $dashboard 后台首页类型
 * @property Status $status 状态 (1正常 2停用)
 * @property string $login_ip 最后登陆IP
 * @property string $login_time 最后登陆时间
 * @property array $backend_setting 后台设置数据
 * @property int $created_by 创建者
 * @property int $updated_by 更新者
 * @property Carbon $created_at 创建时间
 * @property Carbon $updated_at 更新时间
 * @property string $remark 备注
 * @property null|Collection|Role[] $roles
 * @property mixed $password 密码
 */
final class User extends Model
{
    /**
     * The table associated with the model.
     */
    protected ?string $table = 'user';

    /**
     * 隐藏的字段列表.
     * @var string[]
     */
    protected array $hidden = ['password', 'deleted_at'];

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = ['id', 'username', 'password', 'user_type', 'nickname', 'phone', 'email', 'avatar', 'signed', 'dashboard', 'status', 'login_ip', 'login_time', 'backend_setting', 'created_by', 'updated_by', 'created_at', 'updated_at', 'deleted_at', 'remark'];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = [
        'id' => 'integer', 'status' => Status::class, 'user_type' => Type::class,
        'created_by' => 'integer', 'updated_by' => 'integer', 'created_at' => 'datetime',
        'updated_at' => 'datetime', 'backend_setting' => 'json',
    ];

    public function roles(): BelongsToMany
    {
        // @phpstan-ignore-next-line
        return $this->belongsToMany(
            Role::class,
            // @phpstan-ignore-next-line
            Rule::getModel()->getTable(),
            'v0',
            'v1',
            'username',
            'code'
            // @phpstan-ignore-next-line
        )->where(Rule::getModel()->getTable() . '.ptype', '=', 'g');
    }

    public function setPasswordAttribute($value): void
    {
        $this->attributes['password'] = password_hash((string) $value, \PASSWORD_DEFAULT);
    }

    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->password);
    }

    public function creating(Creating $event)
    {
        $this->resetPassword();
    }

    public function resetPassword(): void
    {
        $this->password = 123456;
    }

    public function isSuperAdmin(): bool
    {
        return $this->roles()->where('code', 'SuperAdmin')->exists();
    }

    public function getRoles(): Collection
    {
        return $this->roles()
            ->where('status', Status::ENABLE)
            ->select(['name', 'code', 'remark'])
            ->get();
    }

    public function getMenus(): Collection
    {
        // @phpstan-ignore-next-line
        return $this->roles()->get()->map(static function (Role $role) {
            return $role->menus()->get();
        })->flatten();
    }
}