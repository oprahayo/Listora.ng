<?php

namespace App\Models;

/** @deprecated Use AgentProfile. Kept as a compatibility alias for existing integrations. */
class Agent extends AgentProfile
{
    protected $table = 'agent_profiles';
}
