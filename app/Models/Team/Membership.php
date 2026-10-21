<?php

namespace App\Models\Team;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Relations\Pivot;

#[Table('team_user', incrementing: true)]
class Membership extends Pivot
{
}
