<?php

use App\Livewire\LinkManager;
use App\Models\Link;
use App\Models\User;
use Livewire\Livewire;

test('collection with products is rendered on public profile', function () {
    $user = User::factory()->create();
    $profile = $user->currentWorkspace()?->profile;
    expect($profile)->not->toBeNull();

    $profile->update([
        'slug' => 'shop-'.uniqid(),
        'is_published' => true,
    ]);

    $collection = Link::query()->create([
        'profile_id' => $profile->id,
        'link_type' => 'collection',
        'title' => 'Merch',
        'url' => '#',
        'position' => 1,
        'is_active' => true,
        'opens_in_new_tab' => false,
        'tracking_enabled' => false,
        'show_icon' => false,
    ]);

    Link::query()->create([
        'profile_id' => $profile->id,
        'link_type' => 'product',
        'parent_link_id' => $collection->id,
        'title' => 'Cap Schwarz',
        'url' => 'https://example.com/cap',
        'image_url' => 'https://example.com/cap.jpg',
        'position' => 1,
        'is_active' => true,
        'opens_in_new_tab' => true,
        'tracking_enabled' => true,
        'show_icon' => false,
    ]);

    $this->get(route('public.profile', $profile->slug))
        ->assertOk()
        ->assertSee('Shop', false)
        ->assertSee((string) 1, false)
        ->assertSee('Merch', false)
        ->assertSee('Cap Schwarz', false);
});

test('empty collection shows helper message on public profile', function () {
    $user = User::factory()->create();
    $profile = $user->currentWorkspace()?->profile;
    expect($profile)->not->toBeNull();

    $profile->update([
        'slug' => 'empty-shop-'.uniqid(),
        'is_published' => true,
    ]);

    Link::query()->create([
        'profile_id' => $profile->id,
        'link_type' => 'collection',
        'title' => 'Merch',
        'url' => '#',
        'position' => 1,
        'is_active' => true,
        'opens_in_new_tab' => false,
        'tracking_enabled' => false,
        'show_icon' => false,
    ]);

    $this->get(route('public.profile', $profile->slug))
        ->assertOk()
        ->assertSee('Diese Collection ist noch leer.', false);
});

test('link manager can create collection and product', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(LinkManager::class)
        ->set('collectionTitle', 'Merch')
        ->call('createCollection')
        ->assertHasNoErrors();

    $profile = $user->currentWorkspace()?->profile;
    expect($profile)->not->toBeNull();

    $collection = Link::query()
        ->where('profile_id', $profile->id)
        ->where('link_type', 'collection')
        ->first();

    expect($collection)->not->toBeNull();

    Livewire::actingAs($user)
        ->test(LinkManager::class)
        ->set('productCollectionId', $collection->id)
        ->set('productTitle', 'Cap Schwarz')
        ->set('productUrl', 'https://example.com/cap')
        ->set('productImageUrl', 'https://example.com/cap.jpg')
        ->call('createProduct')
        ->assertHasNoErrors();

    $product = Link::query()
        ->where('profile_id', $profile->id)
        ->where('link_type', 'product')
        ->first();

    expect($product)->not->toBeNull()
        ->and($product->parent_link_id)->toBe($collection->id)
        ->and($product->title)->toBe('Cap Schwarz');
});

test('normal links stay visible beside collections', function () {
    $user = User::factory()->create();
    $profile = $user->currentWorkspace()?->profile;
    expect($profile)->not->toBeNull();

    $profile->update([
        'slug' => 'mixed-shop-'.uniqid(),
        'is_published' => true,
    ]);

    Link::query()->create([
        'profile_id' => $profile->id,
        'link_type' => 'link',
        'title' => 'YouTube',
        'url' => 'https://youtube.com/@creator',
        'position' => 0,
        'is_active' => true,
        'opens_in_new_tab' => true,
        'tracking_enabled' => true,
        'show_icon' => true,
    ]);

    Link::query()->create([
        'profile_id' => $profile->id,
        'link_type' => 'collection',
        'title' => 'Merch',
        'url' => '#',
        'position' => 1,
        'is_active' => true,
        'opens_in_new_tab' => false,
        'tracking_enabled' => false,
        'show_icon' => false,
    ]);

    $this->get(route('public.profile', $profile->slug))
        ->assertOk()
        ->assertSee('YouTube', false)
        ->assertSee('Merch', false);
});
