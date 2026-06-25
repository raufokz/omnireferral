<?php

namespace Database\Seeders;

use App\Models\SeoLandingPage;
use Illuminate\Database\Seeder;

class SeoLandingPageSeeder extends Seeder
{
    public function run(): void
    {
        SeoLandingPage::updateOrCreate(['slug' => 'best-realtor-austin-tx'], [
            'slug' => 'best-realtor-austin-tx',
            'city' => 'Austin',
            'state' => 'TX',
            'primary_keyword' => 'Best Realtor in Austin',
            'secondary_keywords' => [
                'Best Real Estate Agent Austin TX',
                'Top Realtor Austin Texas',
                'Austin Luxury Realtor',
                'Austin Home Buying Expert',
                'Austin Home Selling Expert',
            ],
            'seo_title' => 'Best Realtor in Austin, TX | Trusted Austin Real Estate Expert | iProply',
            'meta_description' => 'Looking for the best Realtor in Austin, Texas? Connect with experienced real estate professionals for buying, selling, relocation, luxury homes, and investment properties.',
            'is_published' => true,
            'content' => [
                'hero_heading' => 'Best Realtor in Austin, TX — Trusted Local Expertise',
                'hero_subheading' => 'Whether you are buying, selling, relocating, or investing, our Austin team delivers personalized real estate guidance backed by decades of local market experience.',
                'agent_name' => 'The Austin iProply Team',
                'agent_title' => 'Austin Real Estate Specialists | iProply',
                'agent_bio' => "With over a decade of combined experience in the Austin metro area, our team has helped hundreds of families buy, sell, and invest in Central Texas real estate. From the rolling hills of Westlake to the vibrant energy of East Austin, we know every neighborhood, every school district, and every market trend that matters.\n\nOur client-first philosophy means you get transparent communication, data-driven strategies, and white-glove service at every step. Whether you are a first-time buyer exploring the Mueller community or a luxury seller in the Davenport Ranch area, we tailor our approach to your unique goals.",
                'why_local_heading' => 'Why Work With a Local Austin Realtor?',
                'why_local_content' => "Austin is not just a city — it is a fast-growing, competitive market where local knowledge makes or breaks a deal. A local Austin Realtor understands the nuances of each neighborhood: from the downtown condos and historic bungalows in Hyde Park to the master-planned communities in Kyle and Buda.\n\nLocal agents have relationships with inspectors, lenders, title companies, and contractors that speed up your transaction. They know which listings are overpriced, which school zones offer the best value, and which areas are poised for appreciation. When you work with someone who lives and breathes Austin real estate, you get an edge that no online search can replicate.",
                'buying_heading' => 'Home Buying Services in Austin',
                'buying_content' => "Finding your dream home in Austin requires more than browsing Zillow. Our home-buying service includes:\n\n- Customized property searches based on your lifestyle, commute, and budget\n- Neighborhood deep-dives covering schools, amenities, safety, and future development\n- Access to off-market and pre-market listings before they hit the MLS\n- Expert negotiation to secure the best price and terms\n- Seamless coordination with lenders, inspectors, and closing attorneys\n- Support through every step — from offer to final walkthrough\n\nWhether you are looking for a starter home in Round Rock, a townhouse in South Congress, or a lakefront property on Lake Travis, we guide you with confidence and clarity.",
                'selling_heading' => 'Home Selling Services in Austin',
                'selling_content' => "Selling your Austin home? We maximize your return with a proven marketing system:\n\n- Professional staging consultation to present your home in its best light\n- High-resolution photography, drone footage, and 3D virtual tours\n- Targeted digital marketing campaigns reaching qualified buyers across Texas and beyond\n- Comparative market analysis to price your home competitively from day one\n- Skilled negotiation to handle multiple offers and contingencies\n- Smooth transaction management through closing\n\nOur homes sell for an average of 97% of asking price because we price right, market aggressively, and negotiate tirelessly on your behalf.",
                'relocation_heading' => 'Relocation Assistance — Moving to Austin',
                'relocation_content' => "Austin is one of the fastest-growing metros in the country, and relocating here can feel overwhelming. We make it easy:\n\n- Personalized area and lifestyle consultations to match you with the right neighborhood\n- School research and enrollment guidance for top-rated districts like Eanes, Round Rock, and Lake Travis ISDs\n- Corporate relocation support for professionals moving with employer assistance\n- Temporary housing referrals and extended-stay options\n- Utility, internet, and service setup coordination\n- Ongoing support until you are fully settled in your new home\n\nFrom California transplants to out-of-state investors, we help thousands of families make Austin home every year.",
                'luxury_heading' => 'Austin Luxury Home Expertise',
                'luxury_content' => "The Austin luxury market demands discretion, sophistication, and elevated marketing. Our luxury services include:\n\n- Exclusive access to off-market and pocket listings in Westlake, Barton Creek, and the Davenport Ranch\n- Bespoke marketing campaigns with editorial photography, cinematic video, and print collateral\n- Private showings by appointment with white-glove hospitality\n- International buyer outreach through luxury portfolio networks\n- Concierge-level coordination with architects, designers, and landscapers\n\nWhether you are buying a $2M estate on Lake Austin or listing a penthouse in the Austonian, we deliver the discretion and results you expect.",
                'investment_heading' => 'Austin Investment Property Guidance',
                'investment_content' => "Austin real estate offers compelling investment opportunities. We help investors:\n\n- Identify high-appreciation neighborhoods with strong rental demand\n- Analyze cap rates, cash flow, and ROI projections\n- Evaluate fix-and-flip opportunities in emerging corridors\n- Connect with property management companies for hands-off investing\n- Build a diversified portfolio of short-term rentals, long-term rentals, and multifamily properties\n\nFrom downtown condos to suburban single-family homes, we provide the data and local insight you need to invest with confidence.",
                'market_heading' => 'Austin Local Market Knowledge',
                'market_content' => "Stay ahead of the Austin real estate market with insider intelligence:\n\n- Current median home price: $550,000 (up 8% year-over-year)\n- Average days on market: 25-35 days for well-priced homes\n- Most active price range: $400k - $800k\n- Fastest-growing areas: Kyle, Buda, Georgetown, and East Austin\n- New construction hotspots: Domain, Mueller, and the South Lamar corridor\n\nWe monitor the market daily so you can make informed decisions with confidence.",
                'service_areas' => [
                    'Austin', 'Round Rock', 'Cedar Park', 'Georgetown', 'Kyle',
                    'Buda', 'San Marcos', 'Lakeway', 'Bee Cave', 'Westlake Hills',
                    'Dripping Springs', 'Pflugerville', 'Leander', 'Bastrop',
                ],
                'faqs' => [
                    ['question' => 'What does the best Realtor in Austin do differently?', 'answer' => 'The best Realtor in Austin brings deep local market knowledge, strong negotiation skills, a proven marketing system, and a client-first approach. They understand Austin\'s unique neighborhoods, pricing trends, and competitive landscape to help you buy or sell with confidence.'],
                    ['question' => 'How do I choose the right Realtor in Austin?', 'answer' => 'Look for an agent with strong local market knowledge, positive client reviews, transparent communication, and experience in your specific type of transaction (buying, selling, luxury, investment, or relocation). Schedule a consultation to ensure their approach aligns with your goals.'],
                    ['question' => 'What is the average home price in Austin?', 'answer' => 'The median home price in Austin is approximately $550,000, though prices vary significantly by neighborhood. Luxury homes in Westlake Hills start around $1.5M, while entry-level homes in Kyle and Buda can be found in the $300k-$400k range.'],
                    ['question' => 'Is Austin a good market for real estate investment?', 'answer' => 'Yes. Austin consistently ranks as one of the strongest real estate investment markets in the US due to population growth, job creation from tech and corporate relocations, and steady home price appreciation. Both short-term and long-term rental markets offer attractive returns.'],
                    ['question' => 'How long does it take to buy a home in Austin?', 'answer' => 'The typical home-buying process in Austin takes 30-45 days from accepted offer to closing, though finding the right property can take anywhere from a few weeks to several months depending on market conditions and your criteria.'],
                ],
                'cta_heading' => 'Ready to Find Your Dream Home in Austin?',
                'cta_subheading' => 'Fill out the form and a local Austin real estate expert will reach out within 24 hours to discuss your goals, answer your questions, and help you take the next step.',
                'form_heading' => 'Contact an Austin Expert',
                'form_subheading' => 'Fill out the form below and we will be in touch shortly.',
                'form_submit_text' => 'Send Message',
            ],
        ]);

        SeoLandingPage::updateOrCreate(['slug' => 'best-realtor-ann-arbor-mi'], [
            'slug' => 'best-realtor-ann-arbor-mi',
            'city' => 'Ann Arbor',
            'state' => 'MI',
            'primary_keyword' => 'Best Realtor in Ann Arbor',
            'secondary_keywords' => [
                'Top Realtor Ann Arbor Michigan',
                'Ann Arbor Real Estate Agent',
                'Ann Arbor Luxury Homes',
                'Ann Arbor Home Buyer Specialist',
                'Ann Arbor Home Seller Specialist',
            ],
            'seo_title' => 'Best Realtor in Ann Arbor, MI | Local Real Estate Expert | iProply',
            'meta_description' => 'Find trusted real estate guidance in Ann Arbor, Michigan. Get expert help with buying, selling, relocation, luxury homes, and investment properties.',
            'is_published' => true,
            'content' => [
                'hero_heading' => 'Best Realtor in Ann Arbor, MI — Local Expertise You Can Trust',
                'hero_subheading' => 'From downtown condos to historic family homes, our Ann Arbor team provides personalized real estate services backed by deep local knowledge and a commitment to your success.',
                'agent_name' => 'The Ann Arbor iProply Team',
                'agent_title' => 'Ann Arbor Real Estate Specialists | iProply',
                'agent_bio' => "Our Ann Arbor team combines decades of local real estate experience with a genuine passion for this vibrant college town. We have helped families, faculty, professionals, and investors navigate Washtenaw County real estate with confidence and clarity.\n\nFrom the historic districts of Burns Park and Old West Side to the growing communities of Pittsfield Township and Scio Township, we know the neighborhoods, schools, and market dynamics that matter. Whether you are a first-time buyer, a relocating professional, or a luxury seller, we bring personalized attention and proven results.",
                'why_local_heading' => 'Why Work With a Local Ann Arbor Realtor?',
                'why_local_content' => "Ann Arbor is a unique market shaped by the University of Michigan, a thriving tech scene, and a strong sense of community. A local Realtor understands the seasonal patterns driven by the academic calendar, the specific appeal of each neighborhood, and the competitive dynamics of this sought-after market.\n\nLocal expertise means you get insights into school district boundaries, commute patterns to Detroit and tech hubs, and the nuances of Ann Arbor's historic preservation rules. When you work with someone who lives and breathes Ann Arbor, you gain a competitive advantage that online tools alone cannot provide.",
                'buying_heading' => 'Home Buying Services in Ann Arbor',
                'buying_content' => "Buying a home in Ann Arbor is exciting, but competition can be fierce. Our home-buying service includes:\n\n- Custom property searches tailored to your lifestyle, commute, and budget\n- Neighborhood tours covering schools, parks, dining, and community amenities\n- Early access to new listings and off-market opportunities\n- Strategic offer preparation and skilled negotiation\n- Coordination with local lenders, inspectors, and closing attorneys\n- Hands-on support from first showing to final walkthrough\n\nWhether you are looking for a cozy bungalow in Water Hill, a downtown condo near campus, or a spacious family home in Dixboro, we help you find the perfect fit.",
                'selling_heading' => 'Home Selling Services in Ann Arbor',
                'selling_content' => "Selling your Ann Arbor home? We deliver results with a comprehensive marketing approach:\n\n- Professional staging and home preparation guidance\n- High-quality photography, videography, and virtual tours\n- Targeted digital and social media marketing campaigns\n- Competitive pricing strategy based on detailed market analysis\n- Expert negotiation to maximize your sale price\n- Smooth transaction management through closing\n\nOur sellers benefit from our deep buyer network and proven track record of successful transactions across Washtenaw County.",
                'relocation_heading' => 'Relocation Assistance — Moving to Ann Arbor',
                'relocation_content' => "Relocating to Ann Arbor? We help you settle in with confidence:\n\n- Comprehensive area and lifestyle consultations to find your ideal neighborhood\n- School research and enrollment guidance for Ann Arbor Public Schools and private options\n- Corporate relocation support for U-M faculty, hospital staff, and tech professionals\n- Temporary housing and extended-stay recommendations\n- Utility, internet, and service setup coordination\n- Ongoing support through your transition\n\nAnn Arbor welcomes new residents with open arms, and we make sure your move is smooth from start to finish.",
                'luxury_heading' => 'Ann Arbor Luxury Home Expertise',
                'luxury_content' => "The Ann Arbor luxury market offers exceptional properties in prestigious neighborhoods. Our luxury services include:\n\n- Discreet access to off-market luxury listings in Barton Hills, Ann Arbor Hills, and Geddes\n- Bespoke marketing with professional editorial photography and luxury portfolio placement\n- Private showings tailored to your schedule and preferences\n- Concierge coordination with architects, designers, and landscape professionals\n- International and national buyer outreach\n\nFrom historic estates to modern waterfront homes, we provide the discretion and expertise that luxury clients expect.",
                'investment_heading' => 'Ann Arbor Investment Property Guidance',
                'investment_content' => "Ann Arbor real estate offers strong investment potential driven by steady demand from the university, medical center, and growing tech sector. We help investors:\n\n- Identify neighborhoods with strong rental demand and appreciation potential\n- Analyze cap rates, cash flow projections, and ROI for rental properties\n- Evaluate fix-and-flip opportunities in emerging areas\n- Connect with trusted property management companies\n- Build a portfolio of single-family rentals, multi-family units, and student housing\n\nAnn Arbor's stable economy and population growth make it a compelling market for real estate investors at every level.",
                'market_heading' => 'Ann Arbor Local Market Knowledge',
                'market_content' => "Key Ann Arbor market insights:\n\n- Current median home price: $475,000\n- Average days on market: 20-30 days for well-priced homes\n- Most active price range: $350k - $700k\n- Highly sought-after neighborhoods: Burns Park, Old West Side, Water Hill, and Barton Hills\n- Growing areas: Pittsfield Township, Scio Township, and Dexter\n\nThe Ann Arbor market moves fast — homes in popular neighborhoods often receive multiple offers within days. Having a local expert on your side is essential.",
                'service_areas' => [
                    'Ann Arbor', 'Dexter', 'Chelsea', 'Saline', 'Ypsilanti',
                    'Pittsfield Township', 'Scio Township', 'Barton Hills',
                    'Milan', 'Manchester', 'Whitmore Lake', 'Pinckney',
                ],
                'faqs' => [
                    ['question' => 'What makes a great Realtor in Ann Arbor?', 'answer' => 'A great Ann Arbor Realtor combines deep local market knowledge, strong negotiation skills, and a genuine commitment to their clients. They understand the unique dynamics of the Ann Arbor market, including the influence of the University of Michigan, seasonal trends, and neighborhood-specific insights.'],
                    ['question' => 'How competitive is the Ann Arbor housing market?', 'answer' => 'The Ann Arbor housing market is highly competitive, especially in desirable neighborhoods near downtown and the university. Well-priced homes often receive multiple offers within the first week. Working with an experienced local agent who can guide your offer strategy is crucial to success.'],
                    ['question' => 'What is the average home price in Ann Arbor?', 'answer' => 'The median home price in Ann Arbor is approximately $475,000, though prices vary significantly by neighborhood. Entry-level homes can be found in the $250k-$350k range, while luxury properties in Barton Hills and Ann Arbor Hills often exceed $1 million.'],
                    ['question' => 'Is Ann Arbor a good place for real estate investment?', 'answer' => 'Absolutely. Ann Arbor offers strong rental demand driven by the University of Michigan, Michigan Medicine, and a growing tech sector. The market has shown consistent appreciation over the long term, making it an attractive market for both rental properties and fix-and-flip investments.'],
                    ['question' => 'How long does it take to close on a home in Ann Arbor?', 'answer' => 'A typical real estate transaction in Ann Arbor closes in 30-45 days from accepted offer. With strong local demand, it is important to have your financing pre-approved and be ready to act quickly when the right property comes to market.'],
                ],
                'cta_heading' => 'Ready to Find Your Dream Home in Ann Arbor?',
                'cta_subheading' => 'Fill out the form and a local Ann Arbor real estate expert will reach out within 24 hours to discuss your goals, answer your questions, and help you take the next step.',
                'form_heading' => 'Contact an Ann Arbor Expert',
                'form_subheading' => 'Fill out the form below and we will be in touch shortly.',
                'form_submit_text' => 'Send Message',
            ],
        ]);

        $this->command->info('SEO landing pages seeded: Austin and Ann Arbor.');
    }
}
