<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Frontend;
use Illuminate\Http\Request;

class PolicyPagesController extends Controller
{
    /**
     * Required policy slugs for the site.
     * Stored in Frontend where data_keys = policy_pages.element
     */
    private function requiredPolicies(): array
    {
        return [
            // Keep existing slugs stable (already used in footer/router)
            'privacy-policy'   => 'Privacy Policy',
            'terms-of-service' => 'Terms And Condition',
            'refund-policy'    => 'Refund Policy',
            'disclaimer'       => 'Disclaimer',
        ];
    }

    /**
     * List policies for Master Admin, and ensure required records exist.
     */
    public function index()
    {
        $temp = activeTemplateName();
        $required = $this->requiredPolicies();

        foreach ($required as $slug => $title) {
            $existing = Frontend::where('data_keys', 'policy_pages.element')
                ->where('slug', $slug)
                ->orderBy('id', 'asc')
                ->get();

            if ($existing->count() > 0) {
                // Keep first row, remove duplicates (prevents ambiguous /policy/{slug})
                $keep = $existing->first();
                $dupes = $existing->slice(1);
                foreach ($dupes as $d) {
                    $d->delete();
                }

                // Ensure it has tempname and at least a title
                $values = $keep->data_values ? (array) $keep->data_values : [];
                $keep->tempname = $keep->tempname ?: $temp;
                $keep->data_values = (object) array_merge([
                    'title' => $title,
                    'details' => $values['details'] ?? ($values['description'] ?? ''),
                    'description' => $values['description'] ?? ($values['details'] ?? ''),
                ], $values);
                $keep->save();
                continue;
            }

            $frontend = new Frontend();
            $frontend->data_keys = 'policy_pages.element';
            $frontend->tempname = $temp;
            $frontend->slug = $slug;
            $frontend->data_values = (object) [
                'title' => $title,
                'details' => '',
                'description' => '',
            ];
            $frontend->save();
        }

        $policies = Frontend::where('data_keys', 'policy_pages.element')
            ->whereIn('slug', array_keys($required))
            ->orderBy('id', 'asc')
            ->get()
            ->map(function ($p) {
                $values = $p->data_values ? (array) $p->data_values : [];
                return [
                    'id' => $p->id,
                    'slug' => $p->slug,
                    'title' => $values['title'] ?? '',
                    'html' => $values['description'] ?? ($values['details'] ?? ''),
                ];
            })
            ->values();

        return response()->json([
            'status' => 'success',
            'message' => ['Policy pages retrieved successfully'],
            'data' => $policies,
        ]);
    }

    /**
     * Update a policy page by slug.
     */
    public function update(Request $request, string $slug)
    {
        $required = $this->requiredPolicies();
        if (!array_key_exists($slug, $required)) {
            return responseError('invalid_policy_slug', ['Invalid policy page']);
        }

        $request->validate([
            'title' => 'nullable|string|max:255',
            'html' => 'nullable|string|max:200000',
        ]);

        $temp = activeTemplateName();

        $policy = Frontend::where('data_keys', 'policy_pages.element')
            ->where('slug', $slug)
            ->orderBy('id', 'asc')
            ->first();

        if (!$policy) {
            $policy = new Frontend();
            $policy->data_keys = 'policy_pages.element';
            $policy->slug = $slug;
        }

        $title = (string) ($request->input('title') ?: $required[$slug]);
        $html = (string) ($request->input('html') ?? '');

        $policy->tempname = $temp;
        $policy->data_values = (object) [
            'title' => $title,
            // Keep both keys for compatibility (blade uses details, SPA uses description)
            'details' => $html,
            'description' => $html,
        ];
        $policy->save();

        return response()->json([
            'status' => 'success',
            'message' => ['Policy page updated successfully'],
            'data' => [
                'id' => $policy->id,
                'slug' => $policy->slug,
                'title' => $title,
                'html' => $html,
            ],
        ]);
    }
}

