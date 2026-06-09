<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @property int $marker_id
 * @property int|null $disaster_id
 * @property string|null $nama
 * @property string|null $path_gambar_marker
 * @property string|null $path_patt
 * @property string|null $path_model
 * @property string|null $path_audio
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Disaster|null $disaster
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ArMarker newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ArMarker newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ArMarker query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ArMarker whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ArMarker whereDisasterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ArMarker whereMarkerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ArMarker whereNama($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ArMarker wherePathAudio($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ArMarker wherePathGambarMarker($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ArMarker wherePathModel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ArMarker wherePathPatt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ArMarker whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperArMarker {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $slug
 * @property string $name
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\DisasterLocation> $locations
 * @property-read int|null $locations_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MitigationStep> $mitigationSteps
 * @property-read int|null $mitigation_steps_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Disaster newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Disaster newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Disaster query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Disaster whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Disaster whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Disaster whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Disaster whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Disaster whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Disaster whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperDisaster {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $disaster_id
 * @property string $location_name
 * @property numeric $latitude
 * @property numeric $longitude
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Disaster $disaster
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DisasterLocation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DisasterLocation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DisasterLocation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DisasterLocation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DisasterLocation whereDisasterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DisasterLocation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DisasterLocation whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DisasterLocation whereLocationName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DisasterLocation whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DisasterLocation whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperDisasterLocation {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $disaster_id
 * @property string $phase
 * @property int $order
 * @property string $content
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Disaster $disaster
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MitigationStep newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MitigationStep newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MitigationStep query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MitigationStep whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MitigationStep whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MitigationStep whereDisasterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MitigationStep whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MitigationStep whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MitigationStep wherePhase($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MitigationStep whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperMitigationStep {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperUser {}
}

