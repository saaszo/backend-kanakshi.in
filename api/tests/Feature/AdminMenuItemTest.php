<?php

namespace Tests\Feature;

use App\Models\MenuItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminMenuItemTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_top_level_external_menu_link(): void
    {
        $admin = User::factory()->create([
            'role' => 'super_admin',
            'status' => 'active',
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($admin)
            ->from(route('admin.menu-items.index'))
            ->post(route('admin.menu-items.store'), [
                'location' => 'header',
                'title' => 'Partner Website',
                'url' => 'https://example.com',
                'parent_id' => '',
                'target' => '',
                'css_class' => '',
                'icon' => '',
                'sort_order' => '',
                'config_json' => '',
                'is_active' => '1',
            ]);

        $response
            ->assertRedirect(route('admin.menu-items.index'))
            ->assertSessionHas('status', 'Menu item added successfully.');

        $this->assertDatabaseHas('menu_items', [
            'location' => 'header',
            'title' => 'Partner Website',
            'url' => 'https://example.com',
            'parent_id' => null,
            'target' => '_self',
            'css_class' => null,
            'icon' => null,
            'sort_order' => 0,
            'is_active' => true,
        ]);
    }

    public function test_admin_cannot_attach_a_child_to_a_parent_from_another_location(): void
    {
        $admin = User::factory()->create([
            'role' => 'super_admin',
            'status' => 'active',
            'is_active' => true,
        ]);

        $parent = MenuItem::query()->create([
            'location' => 'footer',
            'title' => 'Footer Parent',
            'url' => '/footer-parent',
            'target' => '_self',
            'sort_order' => 0,
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($admin)
            ->from(route('admin.menu-items.index'))
            ->post(route('admin.menu-items.store'), [
                'location' => 'header',
                'title' => 'Invalid Child',
                'url' => '/invalid-child',
                'parent_id' => $parent->id,
                'target' => '_self',
                'sort_order' => 1,
                'config_json' => '{}',
                'is_active' => '1',
            ]);

        $response
            ->assertRedirect(route('admin.menu-items.index'))
            ->assertSessionHasErrors('parent_id');

        $this->assertDatabaseMissing('menu_items', [
            'title' => 'Invalid Child',
        ]);
    }
}
