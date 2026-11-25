<?php

namespace Tests\Feature;

use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private function createUser(string $role): User
    {
        return User::factory()->create(['role' => $role]);
    }

    private function createCategory(): MenuCategory
    {
        return MenuCategory::create([
            'name' => 'Test Category',
            'description' => 'Authorization test',
            'is_active' => true,
        ]);
    }

    private function createItem(MenuCategory $category): MenuItem
    {
        return MenuItem::create([
            'menu_category_id' => $category->id,
            'name' => 'Test Item',
            'description' => 'Authorization test',
            'price' => 50,
            'is_available' => true,
        ]);
    }

    private function requests(MenuCategory $category, MenuItem $item): array
    {
        return [
            ['get', route('categories.index'), []],
            ['get', route('categories.create'), []],
            ['post', route('categories.store'), [
                'name' => 'New Category',
                'description' => 'Test',
            ]],
            ['get', route('categories.edit', $category), []],
            ['put', route('categories.update', $category), [
                'name' => 'Updated Category',
                'description' => 'Updated',
            ]],
            ['patch', route('categories.toggle-status', $category), []],

            ['get', route('menu-items.index'), []],
            ['get', route('menu-items.create'), []],
            ['post', route('menu-items.store'), [
                'menu_category_id' => $category->id,
                'name' => 'New Item',
                'description' => 'Test',
                'price' => 60,
            ]],
            ['get', route('menu-items.edit', $item), []],
            ['put', route('menu-items.update', $item), [
                'menu_category_id' => $category->id,
                'name' => 'Updated Item',
                'description' => 'Updated',
                'price' => 70,
            ]],
            ['patch', route('menu-items.toggle-availability', $item), []],
        ];
    }

    private function assertAllRequestsForbidden(
        User $user,
        MenuCategory $category,
        MenuItem $item
    ): void {
        $this->actingAs($user);

        foreach ($this->requests($category, $item) as [$method, $uri, $data]) {
            $this->{$method}($uri, $data)->assertForbidden();
        }
    }

    public function test_cashier_cannot_access_menu_management(): void
    {
        $this->assertAllRequestsForbidden(
            $this->createUser('cashier'),
            $category = $this->createCategory(),
            $this->createItem($category)
        );
    }

    public function test_server_cannot_access_menu_management(): void
    {
        $this->assertAllRequestsForbidden(
            $this->createUser('server'),
            $category = $this->createCategory(),
            $this->createItem($category)
        );
    }

    public function test_owner_can_access_menu_management(): void
    {
        $owner = $this->createUser('owner');
        $category = $this->createCategory();
        $item = $this->createItem($category);

        $this->actingAs($owner);

        $this->get(route('categories.index'))->assertOk();
        $this->get(route('categories.create'))->assertOk();
        $this->get(route('categories.edit', $category))->assertOk();

        $this->post(route('categories.store'), [
            'name' => 'Owner Created Category',
            'description' => 'Created by owner',
        ])->assertRedirect(route('categories.index'));

        $this->put(route('categories.update', $category), [
            'name' => 'Owner Updated Category',
            'description' => 'Updated by owner',
        ])->assertRedirect(route('categories.index'));

        $this->get(route('menu-items.index'))->assertOk();
        $this->get(route('menu-items.create'))->assertOk();
        $this->get(route('menu-items.edit', $item))->assertOk();

        $this->post(route('menu-items.store'), [
            'menu_category_id' => $category->id,
            'name' => 'Owner Created Item',
            'description' => 'Created by owner',
            'price' => 80,
        ])->assertRedirect(route('menu-items.index'));

        $this->put(route('menu-items.update', $item), [
            'menu_category_id' => $category->id,
            'name' => 'Owner Updated Item',
            'description' => 'Updated by owner',
            'price' => 90,
        ])->assertRedirect(route('menu-items.index'));

        $this->patch(route('menu-items.toggle-availability', $item))
            ->assertRedirect(route('menu-items.index'));

        $this->patch(route('categories.toggle-status', $category))
            ->assertRedirect(route('categories.index'));
    }

    public function test_guest_cannot_access_menu_management(): void
    {
        $this->get(route('categories.index'))->assertRedirect(route('login'));
        $this->get(route('menu-items.index'))->assertRedirect(route('login'));
    }

    public function test_cashier_can_still_access_pos(): void
    {
        $this->actingAs($this->createUser('cashier'))
            ->get(route('pos.index'))
            ->assertOk();
    }
}
