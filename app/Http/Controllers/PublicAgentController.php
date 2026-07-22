<?php

namespace App\Http\Controllers;

use App\Models\AgentProfile;
use Illuminate\View\View;

class PublicAgentController extends Controller
{
    public function __invoke(AgentProfile $agentProfile): View
    {
        $agentProfile->load(['properties' => fn ($query) => $query->published()->with(['images', 'amenities'])->latest('published_at')->take(8)]);

        return view('public.agents.show', ['agent' => $agentProfile]);
    }
}
