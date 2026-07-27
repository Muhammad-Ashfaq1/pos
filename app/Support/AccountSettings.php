<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Shared Profile / Password (Account Settings) view data for staff + customer.
 */
final class AccountSettings
{
    /**
     * @return array{first_name: string, last_name: string}
     */
    public static function splitName(?string $name): array
    {
        $parts = preg_split('/\s+/', trim((string) $name), 2) ?: [];

        return [
            'first_name' => $parts[0] ?? '',
            'last_name' => $parts[1] ?? '',
        ];
    }

    public static function combineName(string $firstName, string $lastName): string
    {
        return trim($firstName.' '.$lastName);
    }

    public static function avatarUrl(?object $account): ?string
    {
        $path = $account->avatar ?? null;

        if (! is_string($path) || $path === '') {
            return null;
        }

        return Storage::disk('public')->url($path);
    }

    public static function storeAvatar(object $account, UploadedFile $file): string
    {
        self::deleteAvatarFile($account->avatar ?? null);

        $folder = $account instanceof \App\Models\Customer
            ? 'avatars/customers'
            : 'avatars/users';

        $path = $file->store($folder.'/'.$account->getKey(), 'public');

        $account->forceFill(['avatar' => $path])->save();

        return $path;
    }

    private static function deleteAvatarFile(mixed $path): void
    {
        if (! is_string($path) || $path === '') {
            return;
        }

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public static function viewData(
        object $account,
        string $layout,
        string $active,
    ): array {
        $names = self::splitName($account->name ?? '');

        return [
            'account' => $account,
            'accountSettingsLayout' => $layout,
            'accountSettingsActive' => $active,
            'accountFirstName' => old('first_name', $names['first_name']),
            'accountLastName' => old('last_name', $names['last_name']),
            'accountAvatarUrl' => self::avatarUrl($account),
        ];
    }
}
