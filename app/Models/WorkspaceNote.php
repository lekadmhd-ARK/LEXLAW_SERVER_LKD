<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkspaceNote extends Model
{
    protected $fillable = ['workspace_id', 'user_id', 'title', 'content'];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(TeamWorkspace::class, 'workspace_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
