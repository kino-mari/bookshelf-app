<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReadingPlanTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ログインユーザーは読書計画一覧を表示できる
     */
    public function test_user_can_view_reading_plans(): void
    {
        $user = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'due_date' => today()->addDays(7),
            'status' => 0,
        ]);

        $response = $this->actingAs($user)
            ->get(route('reading-plans.index'));

        $response->assertOk();
        $response->assertViewHas('readingPlans', function ($readingPlans) {
            return $readingPlans->count() === 1;
        });
    }

    /**
     * ユーザーは読書計画を登録できる
     */
    public function test_user_can_create_reading_plan(): void
    {
        $user = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->post(route('reading-plans.store'), [
                'book_id' => $book->id,
                'due_date' => today()->addDays(7)->toDateString(),
            ]);

        $response->assertRedirect(route('reading-plans.index'));

        $this->assertDatabaseHas('reading_plans', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'status' => 0,
        ]);
    }

    /**
     * ユーザーは自分の読書計画を読了にできる
     */
    public function test_user_can_complete_own_reading_plan(): void
    {
        $user = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $plan = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'due_date' => today()->addDays(7),
            'status' => 0,
        ]);

        $response = $this->actingAs($user)
            ->post(route('reading-plans.complete', $plan));

        $response->assertRedirect(route('reading-plans.index'));

        $this->assertDatabaseHas('reading_plans', [
            'id' => $plan->id,
            'status' => 2,
        ]);

        $this->assertNotNull($plan->fresh()->completed_at);
    }

    /**
     * ユーザーは自分の読書計画を削除できる
     */
    public function test_user_can_delete_own_reading_plan(): void
    {
        $user = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $plan = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'due_date' => today()->addDays(7),
            'status' => 0,
        ]);

        $response = $this->actingAs($user)
            ->delete(route('reading-plans.destroy', $plan));

        $response->assertRedirect(route('reading-plans.index'));

        $this->assertDatabaseMissing('reading_plans', [
            'id' => $plan->id,
        ]);
    }

    /**
     * ゲストは読書計画一覧を表示できない
     */
    public function test_guest_cannot_view_reading_plans(): void
    {
        $response = $this->get(route('reading-plans.index'));

        $response->assertRedirect(route('login'));
    }

    /**
     * ユーザーは自分の読書計画の期限を変更できる
     */
    public function test_user_can_update_own_reading_plan_due_date(): void
    {
        $user = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $plan = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'due_date' => today()->addDays(7)->toDateString(),
            'status' => 0,
        ]);

        $newDate = today()->addDays(14)->toDateString();

        $response = $this->actingAs($user)
            ->put(route('reading-plans.update', $plan), [
                'due_date' => $newDate,
            ]);

        $response->assertRedirect(route('reading-plans.index'));

        $this->assertSame(
            $newDate,
            $plan->fresh()->due_date->format('Y-m-d')
        );
    }
}
