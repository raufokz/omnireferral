<?php

namespace Database\Seeders;

use App\Models\ServiceSeoPage;
use Illuminate\Database\Seeder;

class ServiceSeoPageSeeder extends Seeder
{
    public function run(): void
    {
        ServiceSeoPage::updateOrCreate(
            ['slug' => 'pay-at-closing-real-estate-leads'],
            [
                'title' => 'Pay at Closing Real Estate Leads',
                'seo_title' => 'Pay at Closing Real Estate Leads | OmniReferral',
                'meta_description' => 'Get verified real estate leads with no upfront cost. OmniReferral routes buyer and seller leads to agents who pay only when the deal closes.',
                'primary_keyword' => 'pay at closing real estate leads',
                'secondary_keywords' => [
                    'real estate leads pay at closing',
                    'realtor leads pay at closing',
                    'pay per closing real estate leads',
                    'real estate buyer leads pay at closing',
                    'real estate seller leads pay at closing',
                ],
                'hero_title' => 'Get Real Estate Leads You Only Pay For When You Close',
                'hero_body' => "Stop spending money on leads before you've earned a single dollar. OmniReferral delivers pay at closing real estate leads, pre-screened by a real ISA team, matched to your market by ZIP code, and routed straight to your dashboard with zero cost upfront. You only pay a referral fee when the deal actually closes.\n\nWhether you're searching for real estate leads pay at closing, realtor leads pay at closing, or a smarter way to grow your pipeline without draining your ad budget, OmniReferral gives agents and teams a no-risk way to build a consistent flow of qualified buyer and seller leads.",
                'cta_label' => 'Get Your First Leads Today',
                'cta_url' => '/contact',
                'content' => [
                    'sections' => [
                        [
                            'heading' => 'What Are Pay at Closing Real Estate Leads?',
                            'body' => "Pay at closing real estate leads are buyers and sellers that OmniReferral captures, verifies, and hands to you as a qualified referral. You don't pay anything when the lead is delivered. You only pay a referral fee once the transaction closes and you get paid.\n\nIf a deal never closes, you owe nothing. That's the entire model, and it's why this approach has become the starting point for agents who want pipeline without upfront spend.",
                        ],
                        [
                            'heading' => 'How It Works',
                            'body' => "We capture the lead through targeted marketing for active buyers and sellers in your market.\n\nWe verify it with a trained ISA who confirms budget, location, timeline, and real intent.\n\nWe route it to you by ZIP code and deliver it to your dashboard with full context.\n\nYou work the lead under simple agreed terms, then pay only when it closes.",
                        ],
                        [
                            'heading' => 'Best Pay at Closing Real Estate Leads',
                            'body' => "When agents look for the best pay at closing real estate leads, three things matter most: lead quality, routing speed, and whether the leads are actually verified before they reach you.\n\nOmniReferral verifies every lead through live ISA screening before routing. With a lead pool of 181.7 million-plus contacts, 4,750-plus new leads added daily, and an average routing time of about 7 minutes, the platform is built to hand you leads that are ready to convert.",
                        ],
                        [
                            'heading' => 'Pay at Closing Buyer Leads and Seller Leads',
                            'body' => "Seller leads are matched with property context, address, timeline, and motivation so you can enter the conversation prepared.\n\nBuyer leads are pre-qualified on budget and location, so you're not chasing window-shoppers.\n\nBoth buyer and seller referrals can be routed directly to your dashboard as they are verified.",
                        ],
                        [
                            'heading' => 'Pay Per Closing Real Estate Leads',
                            'body' => "Pay per closing real estate leads is the same model described on this page under a different name. You pay a fee only when a transaction closes, never for a lead that does not convert.\n\nThe key point is simple: your cost is tied directly to results. No closed deal, no fee.",
                        ],
                        [
                            'heading' => 'Pay Per Lead Real Estate',
                            'body' => "Pay per lead real estate is a different pricing model. You pay a fixed cost for every lead delivered, whether or not it converts.\n\nOmniReferral is built around pay at closing real estate: no upfront spend and a fee only when you actually close the deal.",
                        ],
                        [
                            'heading' => 'Are Free Pay at Closing Leads Real?',
                            'body' => "Pay at closing leads come with no upfront cost, no setup fee, and no monthly charge with OmniReferral. You only pay a referral fee out of your commission, and only after the deal is done.\n\nIf a provider asks for a fee before that point, it is not a true pay at closing model.",
                        ],
                        [
                            'heading' => 'Why Agents Choose OmniReferral',
                            'body' => "OmniReferral is built around ISA qualification, smart ZIP-based routing, structured tiers, and full context delivery.\n\nEvery lead is verified before it reaches you, matched to the right agent, and delivered with the details you need to follow up with confidence.",
                        ],
                    ],
                    'faqs' => [
                        [
                            'question' => 'What are pay at closing real estate leads?',
                            'answer' => 'They are leads you receive with no upfront cost. You only pay a referral fee once the deal closes.',
                        ],
                        [
                            'question' => 'Is there really no upfront cost?',
                            'answer' => 'Yes. True pay at closing leads do not charge activation, setup, or monthly fees.',
                        ],
                        [
                            'question' => 'What is the difference between pay at closing and pay per lead real estate?',
                            'answer' => 'Pay at closing means you pay only when a deal closes. Pay per lead means you pay a fixed price for every lead whether it converts or not.',
                        ],
                        [
                            'question' => 'Can new agents get pay at closing leads?',
                            'answer' => 'Yes. OmniReferral can support agents who are building their first consistent pipeline.',
                        ],
                        [
                            'question' => 'Do you offer both buyer and seller leads pay at closing?',
                            'answer' => 'Yes. Buyer and seller leads are available and verified before routing.',
                        ],
                        [
                            'question' => 'How do I get started with real estate agent leads pay at closing?',
                            'answer' => 'Submit a request through the contact page and the team can begin reviewing your market and lead fit.',
                        ],
                    ],
                ],
                'is_published' => true,
            ]
        );
    }
}
