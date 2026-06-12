<?php

namespace Tests\Feature;

use App\Models\Testimonial;
use App\Models\User;
use Database\Seeders\TestimonialLibrarySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TestimonialWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_video_testimonial(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create([
            'role' => 'admin',
            'must_reset_password' => false,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.testimonials.store'), [
                'name' => 'Mia Seller',
                'audience' => 'seller',
                'company' => 'Seller Client',
                'location' => 'Miami, FL',
                'rating' => 5,
                'quote' => 'The process felt organized from first outreach to agent handoff.',
                'is_featured' => '1',
                'is_published' => '1',
                'sort_order' => 3,
                'video_file' => UploadedFile::fake()->create('mia-review.mp4', 2048, 'video/mp4'),
            ])
            ->assertRedirect(route('admin.testimonials.index'))
            ->assertSessionHas('success');

        $testimonial = Testimonial::firstOrFail();

        $this->assertSame('seller', $testimonial->audience);
        $this->assertTrue($testimonial->is_featured);
        $this->assertTrue($testimonial->is_published);
        $this->assertNotNull($testimonial->video_url);
        Storage::disk('public')->assertExists($testimonial->video_url);
    }

    public function test_public_testimonials_page_filters_by_audience_and_shows_only_published_entries(): void
    {
        Testimonial::create([
            'name' => 'Buyer Story',
            'audience' => 'buyer',
            'company' => 'Buyer Client',
            'location' => 'Dallas, TX',
            'rating' => 5,
            'quote' => 'Buyer quote visible on the buyer filter.',
            'is_published' => true,
            'video_url' => 'https://youtu.be/abc123xyz00',
        ]);

        Testimonial::create([
            'name' => 'Agent Story',
            'audience' => 'agent',
            'company' => 'Team Lead',
            'location' => 'Austin, TX',
            'rating' => 5,
            'quote' => 'Agent quote should not show on the buyer filter.',
            'is_published' => true,
        ]);

        Testimonial::create([
            'name' => 'Hidden Seller Story',
            'audience' => 'seller',
            'company' => 'Seller Client',
            'location' => 'Houston, TX',
            'rating' => 5,
            'quote' => 'This is a draft and should stay hidden.',
            'is_published' => false,
        ]);

        $this->get(route('reviews', ['audience' => 'buyer']))
            ->assertOk()
            ->assertSee('Buyer Story')
            ->assertDontSee('Agent Story')
            ->assertDontSee('Hidden Seller Story')
            ->assertViewHas('selectedAudience', 'buyer');
    }

    public function test_public_testimonials_page_falls_back_to_recent_reviews_when_category_is_empty(): void
    {
        Testimonial::create([
            'name' => 'Recent Agent Story',
            'audience' => 'agent',
            'company' => 'Team Lead',
            'location' => 'Austin, TX',
            'rating' => 5,
            'quote' => 'Recent approved testimonial shown as a fallback.',
            'is_published' => true,
            'submission_status' => Testimonial::STATUS_APPROVED,
        ]);

        $this->get(route('reviews', ['audience' => 'buyer']))
            ->assertOk()
            ->assertSee('Recent Agent Story')
            ->assertSee('Recent approved testimonials are shown here until this category has its own published reviews.')
            ->assertViewHas('showingFallbackTestimonials', true);
    }

    public function test_public_testimonials_page_paginates_the_library(): void
    {
        for ($index = 1; $index <= 25; $index++) {
            Testimonial::create([
                'name' => 'Buyer Story '.$index,
                'audience' => 'buyer',
                'company' => 'Buyer Client',
                'location' => 'Dallas, TX',
                'rating' => 5,
                'quote' => 'Approved buyer testimonial '.$index.'.',
                'is_published' => true,
                'submission_status' => Testimonial::STATUS_APPROVED,
            ]);
        }

        $this->get(route('reviews'))
            ->assertOk()
            ->assertViewHas('testimonials', function ($testimonials) {
                return $testimonials instanceof LengthAwarePaginator
                    && $testimonials->total() === 25
                    && $testimonials->count() === 24;
            });
    }

    public function test_testimonial_library_seeder_creates_required_public_counts(): void
    {
        $this->seed(TestimonialLibrarySeeder::class);

        $this->assertSame(110, Testimonial::published()->count());
        $this->assertSame(30, Testimonial::published()->where('audience', 'buyer')->count());
        $this->assertSame(30, Testimonial::published()->where('audience', 'seller')->count());
        $this->assertSame(30, Testimonial::published()->where('audience', 'agent')->count());
        $this->assertSame(20, Testimonial::published()->where('audience', 'community')->count());
        $this->assertSame(110, Testimonial::published()->where('rating', 5)->count());
        $this->assertGreaterThanOrEqual(10, Testimonial::published()->where('is_featured', true)->count());
    }
}
