<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Server half of the shared AJAX table (see public/assets/js/pos-table.js).
 *
 * Paginated tables page over fetch(), and the browser URL never changes. Two
 * things the server has to do for that:
 *
 *  • answer a table request with just that table's markup instead of the whole
 *    page — wants() recognises those requests by the header the script sends;
 *  • hand the browser a storage key that identifies WHO is looking, without
 *    putting a user or tenant id in front-end storage — scopeToken() does that.
 *
 * A fragment response is an optimisation, not a contract: the script picks its
 * table out of whatever HTML comes back, so a controller that ignores wants()
 * still paginates correctly (it just re-renders more than it needs to).
 */
class TableFragment
{
    /** Sent by pos-table.js, carrying the id of the table being paged. */
    public const HEADER = 'X-POS-Table';

    /**
     * Is this request asking for one specific table's markup?
     */
    public static function wants(Request $request, string $tableId): bool
    {
        return $request->header(self::HEADER) === $tableId;
    }

    /**
     * Opaque per-viewer key segment for sessionStorage.
     */
    public static function scopeToken(): string
    {
        $user = Auth::user();

        return substr(hash_hmac(
            'sha256',
            (string) ($user->id ?? 0).'|'.(string) ($user->tenant_id ?? 0),
            (string) config('app.key'),
        ), 0, 16);
    }
}
