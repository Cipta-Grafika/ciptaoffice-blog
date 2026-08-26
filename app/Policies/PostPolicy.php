<?php

namespace App\Policies;

use App\Enums\PostStatus;
use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    /**
     * Memberikan akses penuh kepada administrator sebelum pemeriksaan ability lainnya dijalankan.
     *
     * @param  User  $user  Pengguna terautentikasi yang meminta akses.
     * @return bool|null True jika pengguna adalah administrator; null agar policy melanjutkan ke method ability terkait.
     */
    public function before(User $user): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    /**
     * Menentukan apakah pengguna dapat melihat daftar artikel CMS.
     *
     * @param  User  $user  Pengguna terautentikasi yang meminta akses.
     * @return bool True jika akun pengguna aktif; false jika akun tidak aktif.
     */
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    /**
     * Menentukan apakah pengguna dapat melihat sebuah artikel CMS.
     *
     * @param  User  $user  Pengguna terautentikasi yang meminta akses.
     * @param  Post  $post  Artikel yang hendak dilihat.
     * @return bool True jika pengguna merupakan pemilik artikel; false jika artikel dimiliki pengguna lain.
     */
    public function view(User $user, Post $post): bool
    {
        return $post->author_id === $user->id;
    }

    /**
     * Menentukan apakah pengguna dapat membuat artikel baru.
     *
     * @param  User  $user  Pengguna terautentikasi yang meminta akses.
     * @return bool True jika akun pengguna aktif; false jika akun tidak aktif.
     */
    public function create(User $user): bool
    {
        return $user->is_active;
    }

    /**
     * Menentukan apakah pengguna dapat memperbarui sebuah artikel.
     *
     * @param  User  $user  Pengguna terautentikasi yang meminta akses.
     * @param  Post  $post  Artikel yang hendak diperbarui.
     * @return bool True jika pengguna adalah pemilik dan artikel berstatus draft atau returned; selain itu false.
     */
    public function update(User $user, Post $post): bool
    {
        return $post->author_id === $user->id && in_array($post->status, [PostStatus::Draft, PostStatus::Returned], true);
    }

    /**
     * Menentukan apakah pengguna dapat memindahkan sebuah artikel ke sampah.
     *
     * @param  User  $user  Pengguna terautentikasi yang meminta akses.
     * @param  Post  $post  Artikel yang hendak dipindahkan ke sampah.
     * @return bool Mengikuti hasil izin update: true untuk artikel milik pengguna yang masih dapat diedit; selain itu false.
     */
    public function delete(User $user, Post $post): bool
    {
        return $this->update($user, $post);
    }

    /**
     * Menentukan apakah pengguna dapat menghapus sebuah artikel secara permanen.
     *
     * @param  User  $user  Pengguna terautentikasi yang meminta akses.
     * @param  Post  $post  Artikel yang hendak dihapus permanen.
     * @return bool True jika pengguna adalah administrator; false untuk role lainnya.
     */
    public function forceDelete(User $user, Post $post): bool
    {
        return $user->isAdmin();
    }

    /**
     * Menentukan apakah pengguna dapat mengajukan sebuah artikel untuk ditinjau.
     *
     * @param  User  $user  Pengguna terautentikasi yang meminta akses.
     * @param  Post  $post  Artikel yang hendak diajukan untuk ditinjau.
     * @return bool Mengikuti hasil izin update: true untuk artikel milik pengguna yang masih dapat diedit; selain itu false.
     */
    public function submit(User $user, Post $post): bool
    {
        return $this->update($user, $post);
    }
}
