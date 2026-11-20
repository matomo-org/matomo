<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\DebugView;

use Piwik\Piwik;

/**
 * Derives the stream-row fields of a Debug View hit (type, title, subtitle)
 * from the raw tracking request parameters as they were received by
 * matomo.php. The enriched details (goal names, processed action data, visit
 * context) are loaded lazily by the UI through the Live API when a hit is
 * opened.
 *
 * All methods are pure functions of their input, which keeps them unit
 * testable.
 */
class HitFlattener
{
    /**
     * Hit types that have an own DebugView_Type* translation.
     */
    public const TRANSLATED_TYPES = [
        'pageview', 'event', 'goal', 'download', 'outlink', 'search',
        'ecommerceOrder', 'ecommerceAbandonedCart', 'content', 'ping',
        'media', 'form', 'sessionRecording', 'crash',
    ];

    /**
     * Action types of other plugins' Tracker\Action subclasses. Deliberately
     * hard-coded: the ids are stable public constants
     * (MediaAnalytics\Actions\ActionMedia::TYPE_MEDIA etc.) and mapping them
     * here avoids exposing a new API just for type names.
     */
    public const PLUGIN_ACTION_TYPES = [
        94  => 'media',
        95  => 'form',
        96  => 'sessionRecording',
        110 => 'crash',
    ];

    /**
     * Tracking parameters are attacker-controlled and PHP parses e.g.
     * e_c[]=x into an array — a stored array value must never reach scalar
     * string functions. Returns '' for any non-scalar value.
     *
     * @param mixed $value
     */
    public function toScalarString($value): string
    {
        return is_string($value) || is_int($value) || is_float($value) ? (string) $value : '';
    }

    /**
     * Derives the hit type. The action type recorded by the tracker (the
     * Tracker\Action subclass the request was handled by) wins for other
     * plugins' request kinds; core requests are derived from the raw query
     * parameters, following the tracker's own precedence for requests that
     * carry several markers.
     */
    public function deriveType(array $query, ?int $actionType = null): string
    {
        if ($actionType !== null && isset(self::PLUGIN_ACTION_TYPES[$actionType])) {
            return self::PLUGIN_ACTION_TYPES[$actionType];
        }
        if ($this->toScalarString($query['ping'] ?? '') === '1') {
            return 'ping';
        }
        if (!empty($query['download'])) {
            return 'download';
        }
        if (!empty($query['link'])) {
            return 'outlink';
        }
        if (isset($query['search']) && $query['search'] !== '') {
            return 'search';
        }
        if (!empty($query['e_c']) || !empty($query['e_a'])) {
            return 'event';
        }
        if (!empty($query['c_n']) || !empty($query['c_p'])) {
            return 'content';
        }
        $idGoal = $this->toScalarString($query['idgoal'] ?? '');
        if (isset($query['idgoal']) && $idGoal !== '') {
            if ($idGoal === '0') {
                return !empty($query['ec_id']) ? 'ecommerceOrder' : 'ecommerceAbandonedCart';
            }
            return 'goal';
        }
        if (!empty($query['action_name']) || !empty($query['url'])) {
            return 'pageview';
        }

        return 'other';
    }

    public function buildTitle(array $query, string $type): string
    {
        switch ($type) {
            case 'event':
                $parts = array_filter([
                    $this->toScalarString($query['e_c'] ?? ''),
                    $this->toScalarString($query['e_a'] ?? ''),
                    $this->toScalarString($query['e_n'] ?? ''),
                ], static function (string $value): bool {
                    return $value !== '';
                });
                if (!empty($parts)) {
                    return implode(' – ', $parts);
                }
                break;
            case 'search':
                if (isset($query['search']) && is_string($query['search']) && $query['search'] !== '') {
                    return $query['search'];
                }
                break;
            case 'goal':
                return Piwik::translate('DebugView_TypeGoal') . ' #'
                    . (int) $this->toScalarString($query['idgoal'] ?? '0');
            case 'ecommerceOrder':
                if (!empty($query['ec_id']) && is_string($query['ec_id'])) {
                    return $query['ec_id'];
                }
                break;
            case 'content':
                $parts = array_filter([
                    $this->toScalarString($query['c_n'] ?? ''),
                    $this->toScalarString($query['c_i'] ?? ''),
                ], static function (string $value): bool {
                    return $value !== '';
                });
                if (!empty($parts)) {
                    return implode(' – ', $parts);
                }
                break;
            case 'media':
                if (!empty($query['ma_ti']) && is_string($query['ma_ti'])) {
                    return $query['ma_ti'];
                }
                break;
            case 'form':
                if (!empty($query['fa_name']) && is_string($query['fa_name'])) {
                    return $query['fa_name'];
                }
                break;
            case 'crash':
                if (!empty($query['cra']) && is_string($query['cra'])) {
                    return $query['cra'];
                }
                break;
            case 'pageview':
            case 'ping':
                foreach (['action_name', 'url'] as $field) {
                    if (!empty($query[$field]) && is_string($query[$field])) {
                        return $query[$field];
                    }
                }
                break;
        }

        foreach (['action_name', 'url'] as $field) {
            if (!empty($query[$field]) && is_string($query[$field])) {
                return $query[$field];
            }
        }

        $key = in_array($type, self::TRANSLATED_TYPES, true)
            ? 'DebugView_Type' . ucfirst($type)
            : 'DebugView_TypeVendor';

        return Piwik::translate($key);
    }

    public function buildSubtitle(array $query, string $type): string
    {
        switch ($type) {
            case 'search':
                $category = $query['search_cat'] ?? '';
                return is_string($category) ? $category : '';
            case 'download':
                $url = $query['download'] ?? '';
                return is_string($url) ? $url : '';
            case 'outlink':
                $url = $query['link'] ?? '';
                return is_string($url) ? $url : '';
        }

        $url = $query['url'] ?? '';

        return is_string($url) ? $url : '';
    }
}
