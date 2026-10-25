<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property int $id
 * @property int $owner_id
 * @property string $name
 * @property string|null $description
 * @property string $status
 * @property array<int, string> $pillars
 */
class Project extends Model
{
    protected $fillable = ['owner_id', 'name', 'description', 'status', 'pillars', 'archived_at'];

    protected function casts(): array
    {
        return ['pillars' => 'array', 'archived_at' => 'datetime'];
    }

    /** @return BelongsTo<User, $this> */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /** @return BelongsToMany<User, $this, Pivot, 'pivot'> */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_members')->withPivot('role')->withTimestamps();
    }

    public function isOwnedBy(User $user): bool
    {
        return $this->owner_id === $user->id;
    }

    public function hasEditor(User $user): bool
    {
        return $this->members()->whereKey($user->id)->wherePivot('role', 'editor')->exists();
    }

    /** @return HasMany<WritingAssignment, $this> */
    public function assignments(): HasMany
    {
        return $this->hasMany(WritingAssignment::class);
    }

    /** @return HasMany<LiteratureSource, $this> */
    public function literatureSources(): HasMany
    {
        return $this->hasMany(LiteratureSource::class);
    }

    /** @return HasMany<InstrumentBlueprint, $this> */
    public function instrumentBlueprints(): HasMany
    {
        return $this->hasMany(InstrumentBlueprint::class);
    }

    /** @return HasMany<GuruBahasaMaterial, $this> */
    public function guruBahasaMaterials(): HasMany
    {
        return $this->hasMany(GuruBahasaMaterial::class);
    }

    public function corpus(): HasOne
    {
        return $this->hasOne(Corpus::class);
    }
}
