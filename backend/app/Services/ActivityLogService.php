<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityLogService
{
    /**
     * Log an activity.
     */
    public static function log(
        string $action,
        ?Model $model = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $description = null,
        ?Request $request = null
    ): ActivityLog {
        $user = Auth::user();
        $request = $request ?? request();

        $data = [
            'user_id' => $user?->id,
            'action' => $action,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'description' => $description ?? self::generateDescription($action, $model, $user),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ];

        if ($model) {
            $data['model_type'] = get_class($model);
            $data['model_id'] = $model->id;
        }

        return ActivityLog::create($data);
    }

    /**
     * Log a model creation.
     */
    public static function logCreated(Model $model, ?Request $request = null): ActivityLog
    {
        $modelName = class_basename($model);
        $description = sprintf('%s created: %s', $modelName, self::getModelIdentifier($model));

        return self::log('created', $model, null, $model->getAttributes(), $description, $request);
    }

    /**
     * Log a model update.
     */
    public static function logUpdated(Model $model, array $oldAttributes, ?Request $request = null): ActivityLog
    {
        $modelName = class_basename($model);
        $description = sprintf('%s updated: %s', $modelName, self::getModelIdentifier($model));

        $newAttributes = array_intersect_key($model->getAttributes(), $oldAttributes);

        return self::log('updated', $model, $oldAttributes, $newAttributes, $description, $request);
    }

    /**
     * Log a model deletion.
     */
    public static function logDeleted(Model $model, ?Request $request = null): ActivityLog
    {
        $modelName = class_basename($model);
        $description = sprintf('%s deleted: %s', $modelName, self::getModelIdentifier($model));

        return self::log('deleted', $model, $model->getAttributes(), null, $description, $request);
    }

    /**
     * Log user login.
     */
    public static function logLogin(?User $user = null, ?Request $request = null): ActivityLog
    {
        $user = $user ?? Auth::user();
        $description = sprintf('User logged in: %s (%s)', $user->name, $user->email);

        return self::log('login', null, null, null, $description, $request);
    }

    /**
     * Log user logout.
     */
    public static function logLogout(?User $user = null, ?Request $request = null): ActivityLog
    {
        $user = $user ?? Auth::user();
        $description = sprintf('User logged out: %s (%s)', $user->name, $user->email);

        return self::log('logout', null, null, null, $description, $request);
    }

    /**
     * Generate a default description for an action.
     */
    private static function generateDescription(string $action, ?Model $model, ?User $user): string
    {
        $userName = $user ? $user->name : 'System';
        $modelName = $model ? class_basename($model) : 'Unknown';

        return sprintf('%s %s %s', $userName, $action, $modelName);
    }

    /**
     * Get a string identifier for a model.
     */
    private static function getModelIdentifier(Model $model): string
    {
        if (method_exists($model, 'getRouteKeyName')) {
            $key = $model->getRouteKeyName();
            if (isset($model->{$key})) {
                return (string) $model->{$key};
            }
        }

        // Try common identifier fields
        foreach (['name', 'email', 'title', 'id'] as $field) {
            if (isset($model->{$field})) {
                return (string) $model->{$field};
            }
        }

        return 'ID: ' . $model->id;
    }
}
