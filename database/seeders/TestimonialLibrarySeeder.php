<?php

namespace Database\Seeders;

use App\Models\Testimonial;
use Illuminate\Database\Seeder;

class TestimonialLibrarySeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->groups() as $audience => $group) {
            for ($index = 0; $index < $group['count']; $index++) {
                $number = $index + 1;
                $createdAt = now()->subDays(($group['count'] - $index) + $group['sort_offset']);
                $role = $group['roles'][$index % count($group['roles'])];
                $city = $group['cities'][$index % count($group['cities'])];
                $moment = $group['moments'][$index % count($group['moments'])];
                $outcome = $group['outcomes'][$index % count($group['outcomes'])];
                $detail = $group['details'][$index % count($group['details'])];

                $payload = [
                    'name' => $group['names'][$index],
                    'audience' => $audience,
                    'company' => $role,
                    'location' => $city,
                    'submitted_by_email' => sprintf('seed-%s-%02d@example.test', $audience, $number),
                    'submitted_by_user_id' => null,
                    'photo' => 'images/reviews/review-'.(($index % 4) + 1).'.svg',
                    'rating' => 5,
                    'quote' => sprintf($group['templates'][$index % count($group['templates'])], $moment, $outcome)
                        .' As a '.$role.' in '.$city.', '.$detail.'.',
                    'audio_path' => null,
                    'video_url' => null,
                    'is_featured' => in_array($number, $group['featured'], true),
                    'is_published' => true,
                    'sort_order' => $group['sort_offset'] + $number,
                    'submission_status' => Testimonial::STATUS_APPROVED,
                    'reviewed_by_user_id' => null,
                    // Ensure reviewed_at is always a valid SQL datetime (avoid edge cases with older/invalid existing rows)
                    'reviewed_at' => $createdAt ? $createdAt->copy()->setSecond(0) : now(),
                    'created_at' => $createdAt,
                    'updated_at' => now(),
                ];

                Testimonial::updateOrCreate(
                    ['submitted_by_email' => $payload['submitted_by_email']],
                    $payload
                );
            }
        }
    }

    private function groups(): array
    {
        return [
            Testimonial::AUDIENCE_BUYER => [
                'count' => 30,
                'sort_offset' => 100,
                'featured' => [1, 8, 15, 22, 29],
                'names' => [
                    'Avery Brooks', 'Maya Chen', 'Landon Pierce', 'Nora Ellis', 'Theo Martin',
                    'Isla Bennett', 'Caleb Ortiz', 'Sienna Gray', 'Evan Carter', 'Lila Morgan',
                    'Julian Reed', 'Amara Lewis', 'Micah Hayes', 'Elena Foster', 'Rowan Price',
                    'Mila Sullivan', 'Dylan Harper', 'Naomi Cruz', 'Gavin Wright', 'Clara Vaughn',
                    'Eli Dawson', 'Zara Mitchell', 'Owen Parker', 'Talia Rivers', 'Miles Grant',
                    'Arielle Lane', 'Jonah Scott', 'Leah Kim', 'Mateo Collins', 'Sage Turner',
                ],
                'roles' => ['First-Time Buyer', 'Relocation Buyer', 'Move-Up Buyer', 'Condo Buyer', 'Investor Buyer'],
                'cities' => [
                    'Dallas, TX', 'Tampa, FL', 'Phoenix, AZ', 'Atlanta, GA', 'Raleigh, NC',
                    'Austin, TX', 'Orlando, FL', 'Charlotte, NC', 'Nashville, TN', 'Scottsdale, AZ',
                ],
                'moments' => [
                    'understand what mattered before we spoke with an agent',
                    'compare neighborhoods without feeling rushed',
                    'move from online browsing to a serious next step',
                    'share our timing and budget with more confidence',
                    'stay organized while relocating from out of state',
                ],
                'outcomes' => [
                    'the first agent conversation felt prepared and useful',
                    'every follow-up had a clear reason behind it',
                    'we stopped repeating the same details to different people',
                    'the process felt calmer and more trustworthy',
                    'the handoff felt personal instead of automated',
                ],
                'details' => [
                    'the experience gave us a practical plan instead of more noise',
                    'we always understood why the next step mattered',
                    'the handoff made the search feel more focused',
                    'the communication felt calm even when timing was tight',
                    'the process helped us make decisions with confidence',
                    'we felt like our needs were actually read before anyone called',
                ],
                'templates' => [
                    'OmniReferral helped us %s, and %s.',
                    'The buyer intake was simple but thoughtful. It helped us %s, so %s.',
                    'We came in with a lot of questions. OmniReferral helped us %s, and %s.',
                    'The experience felt organized from the first step. We were able to %s, and %s.',
                    'What stood out was the clarity. OmniReferral helped us %s, which meant %s.',
                ],
            ],
            Testimonial::AUDIENCE_SELLER => [
                'count' => 30,
                'sort_offset' => 200,
                'featured' => [2, 9, 16, 23, 30],
                'names' => [
                    'Marcus Hale', 'Priya Nelson', 'Camden Ross', 'Sofia Bell', 'Andre Mason',
                    'Vivian Cole', 'Grant Fisher', 'Noelle Bryant', 'Emmett Shaw', 'Ivy Wallace',
                    'Hannah Blake', 'Roman Diaz', 'Keira Stone', 'Felix Monroe', 'Aaliyah James',
                    'Bennett Wells', 'Maren Brooks', 'Silas Hughes', 'Tessa Ford', 'Adrian Quinn',
                    'Jade Spencer', 'Cole Bennett', 'Mira Weston', 'Finn Archer', 'Paige Holland',
                    'Rafael Dean', 'Selena Hart', 'Wyatt Palmer', 'Daphne Reed', 'Nico Warren',
                ],
                'roles' => ['Seller Client', 'Home Seller', 'Property Owner', 'Listing Client', 'Move-Out Seller'],
                'cities' => [
                    'Miami, FL', 'Houston, TX', 'Las Vegas, NV', 'Jacksonville, FL', 'Denver, CO',
                    'San Antonio, TX', 'Fort Worth, TX', 'Mesa, AZ', 'Columbus, OH', 'Savannah, GA',
                ],
                'moments' => [
                    'explain our goals before the listing conversation',
                    'organize the details that usually get scattered',
                    'feel prepared for pricing and next-step questions',
                    'move quickly without losing a premium feel',
                    'connect with someone who already understood the situation',
                ],
                'outcomes' => [
                    'the follow-up felt polished and professional',
                    'the agent handoff was much smoother than expected',
                    'we felt more confident before making decisions',
                    'communication stayed clear from start to finish',
                    'the entire experience felt more credible',
                ],
                'details' => [
                    'the team respected our timeline and kept expectations clear',
                    'we had cleaner notes ready before the listing conversation',
                    'the process reduced the back-and-forth that usually slows things down',
                    'the first follow-up already reflected what we had shared',
                    'the experience felt organized without feeling impersonal',
                    'we could tell the handoff was built for a real seller conversation',
                ],
                'templates' => [
                    'OmniReferral gave us a better way to %s, and %s.',
                    'Selling can get noisy, but this process helped us %s. As a result, %s.',
                    'The seller experience felt polished. We could %s, and %s.',
                    'We appreciated how quickly the team helped us %s. From there, %s.',
                    'The handoff was the strongest part. OmniReferral helped us %s, so %s.',
                ],
            ],
            Testimonial::AUDIENCE_AGENT => [
                'count' => 30,
                'sort_offset' => 300,
                'featured' => [3, 10, 17, 24],
                'names' => [
                    'Jordan Miles', 'Kendall Reeves', 'Riley Sutton', 'Morgan Blake', 'Taylor Finch',
                    'Alexis Moore', 'Cameron Hayes', 'Reese Porter', 'Drew Lawson', 'Parker Silva',
                    'Emery Brooks', 'Quinn Carter', 'Hayden Ellis', 'Skyler James', 'Logan Pierce',
                    'Harper Lane', 'Casey Morgan', 'Blake Rivera', 'Ari Jordan', 'Dakota Gray',
                    'Rowan Bennett', 'Jules Foster', 'Milan Price', 'Remy Cole', 'Shawn Walker',
                    'Devon Cruz', 'Jamie Ellis', 'Avery Quinn', 'Robin Hart', 'Sloan Parker',
                ],
                'roles' => ['Broker Associate', 'Team Lead', 'Listing Specialist', 'Buyer Specialist', 'Managing Broker'],
                'cities' => [
                    'Boca Raton, FL', 'Dallas, TX', 'Austin, TX', 'Atlanta, GA', 'Charlotte, NC',
                    'Phoenix, AZ', 'Tampa, FL', 'Orlando, FL', 'Nashville, TN', 'Raleigh, NC',
                ],
                'moments' => [
                    'understand lead intent before the first call',
                    'separate serious opportunities from casual inquiries',
                    'give our team better notes before outreach',
                    'protect follow-up time for the right conversations',
                    'keep the client handoff consistent across the team',
                ],
                'outcomes' => [
                    'our first conversations became much stronger',
                    'we spent less time chasing vague leads',
                    'the team could follow up faster and with better context',
                    'conversion quality improved without adding more admin work',
                    'the experience felt more like a partner workflow than a lead list',
                ],
                'details' => [
                    'the notes helped our agents personalize outreach immediately',
                    'the quality control made the opportunity easier to prioritize',
                    'the process fit naturally into our existing follow-up rhythm',
                    'our team had fewer cold starts and better opening conversations',
                    'the platform helped us protect time for real client work',
                    'the consistency across handoffs made coaching easier',
                ],
                'templates' => [
                    'OmniReferral helped us %s, and %s.',
                    'The biggest change for our team was context. We could %s, so %s.',
                    'The lead flow feels more intentional now. OmniReferral helps us %s, and %s.',
                    'For agents, speed only matters when the context is good. This helped us %s, which meant %s.',
                    'Our team needed cleaner handoffs. OmniReferral helped us %s, and %s.',
                ],
            ],
            Testimonial::AUDIENCE_COMMUNITY => [
                'count' => 20,
                'sort_offset' => 400,
                'featured' => [4, 11, 18],
                'names' => [
                    'Iris Monroe', 'Graham Ellis', 'Maya Stone', 'Leo Carter', 'Nina Wells',
                    'Omar Bennett', 'Vera Quinn', 'Theo Brooks', 'Malia Foster', 'Cruz Harper',
                    'Elise Palmer', 'Dante Reed', 'June Porter', 'Kai Sullivan', 'Lena Wright',
                    'Miles Chen', 'Rhea Collins', 'Samir Grant', 'Tori Lane', 'Wesley Park',
                ],
                'roles' => ['Community Member', 'Referral Partner', 'Platform User', 'Local Partner', 'Network Member'],
                'cities' => [
                    'Chicago, IL', 'Minneapolis, MN', 'Richmond, VA', 'Portland, OR', 'Salt Lake City, UT',
                    'Kansas City, MO', 'Greenville, SC', 'Boise, ID', 'Madison, WI', 'Knoxville, TN',
                ],
                'moments' => [
                    'see how each side of the referral journey fits together',
                    'share feedback without needing a complicated account flow',
                    'trust that the platform was built around real follow-through',
                    'understand the difference between a lead and a qualified handoff',
                    'connect local needs with a cleaner referral experience',
                ],
                'outcomes' => [
                    'the platform felt active, credible, and easy to understand',
                    'the communication felt more human than a typical marketplace',
                    'the experience made the network feel more dependable',
                    'the public pages gave us confidence in the process',
                    'it was easy to see why agents and clients would use it again',
                ],
                'details' => [
                    'the experience made the network feel approachable',
                    'the public proof helped the platform feel more established',
                    'the flow made it easy to understand where each request goes',
                    'the process felt designed around accountability',
                    'the platform gave local users a clearer path to the right person',
                    'the experience connected practical needs with a polished handoff',
                ],
                'templates' => [
                    'OmniReferral makes it easier to %s, and %s.',
                    'From a community perspective, the strongest part is clarity. It helps people %s, so %s.',
                    'The platform feels thoughtfully organized. It helped us %s, and %s.',
                    'The experience does a good job helping people %s. Because of that, %s.',
                    'What stood out was trust. OmniReferral helps users %s, and %s.',
                ],
            ],
        ];
    }
}
