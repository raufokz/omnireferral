<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $recipientType === 'admin' ? 'New Enquiry: ' . $property->title : 'New Enquiry on Your Listing: ' . $property->title }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: #f8f9fa; padding: 30px; border-radius: 8px;">
        <h1 style="color: #0b3668; margin-top: 0;">
            {{ $recipientType === 'admin' ? 'New Property Enquiry' : 'New Enquiry on Your Listing' }}
        </h1>
        
        <p style="margin-bottom: 20px;">
            {{ $recipientType === 'admin' ? 'A new enquiry has been submitted for the following property:' : 'You have received a new enquiry on your listing:' }}
        </p>

        <div style="background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #ff6b35;">
            <h2 style="color: #0b3668; margin-top: 0; font-size: 18px;">{{ $property->title }}</h2>
            <p style="margin: 5px 0; color: #666;">
                <strong>Location:</strong> {{ $property->location }}
            </p>
            <p style="margin: 5px 0; color: #666;">
                <strong>Price:</strong> {{ $property->formattedPrice() }}
            </p>
            <p style="margin: 5px 0;">
                <a href="{{ route('properties.show', $property) }}" style="color: #ff6b35; text-decoration: none;">View Property →</a>
            </p>
        </div>

        <h3 style="color: #0b3668; margin-bottom: 15px;">Enquiry Details</h3>
        
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
            <tr>
                <td style="padding: 8px 0; border-bottom: 1px solid #e2e8f0; font-weight: bold; width: 150px;">Name:</td>
                <td style="padding: 8px 0; border-bottom: 1px solid #e2e8f0;">{{ $lead->name }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; border-bottom: 1px solid #e2e8f0; font-weight: bold;">Email:</td>
                <td style="padding: 8px 0; border-bottom: 1px solid #e2e8f0;">
                    <a href="mailto:{{ $lead->email }}" style="color: #ff6b35; text-decoration: none;">{{ $lead->email }}</a>
                </td>
            </tr>
            <tr>
                <td style="padding: 8px 0; border-bottom: 1px solid #e2e8f0; font-weight: bold;">Phone:</td>
                <td style="padding: 8px 0; border-bottom: 1px solid #e2e8f0;">{{ $lead->phone }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; border-bottom: 1px solid #e2e8f0; font-weight: bold;">Enquiry Type:</td>
                <td style="padding: 8px 0; border-bottom: 1px solid #e2e8f0;">
                    {{ \Illuminate\Support\Str::headline(str_replace('_', ' ', $lead->enquiry_type)) }}
                </td>
            </tr>
            <tr>
                <td style="padding: 8px 0; font-weight: bold;">Message:</td>
                <td style="padding: 8px 0;">{{ $lead->message }}</td>
            </tr>
        </table>

        <p style="margin-bottom: 20px;">
            <strong>Lead Number:</strong> {{ $lead->lead_number }}<br>
            <strong>Submitted:</strong> {{ $lead->created_at->format('F j, Y \a\t g:i A') }}
        </p>

        <div style="background: #e7f3ff; padding: 15px; border-radius: 8px; border-left: 4px solid #0b3668;">
            <p style="margin: 0; color: #0b3668; font-size: 14px;">
                <strong>Next Steps:</strong> Contact the enquirer promptly to provide the information they requested.
            </p>
        </div>

        <hr style="border: none; border-top: 1px solid #e2e8f0; margin: 30px 0;">

        <p style="color: #666; font-size: 14px; margin-bottom: 5px;">
            This is an automated notification from OmniReferral.
        </p>
        <p style="color: #666; font-size: 14px; margin: 0;">
            {{ config('app.name') }} · {{ url('/') }}
        </p>
    </div>
</body>
</html>
