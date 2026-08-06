<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRM MVP Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f3f4f6; }
    </style>
</head>
<body class="text-gray-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-8 text-center">CRM Lead Pipeline</h1>
        
        <div class="flex flex-col md:flex-row gap-6">
            <!-- Follow Up Column -->
            <div class="flex-1 bg-white rounded-lg shadow p-4 border-t-4 border-blue-500">
                <h2 class="text-lg font-semibold border-b pb-2 mb-4 text-blue-600 flex justify-between items-center">
                    Follow Up
                    <span class="bg-blue-100 text-blue-800 text-xs py-1 px-2 rounded-full">{{ $leads->where('stage', 'Follow Up')->count() }}</span>
                </h2>
                <div class="space-y-4">
                    @foreach($leads->where('stage', 'Follow Up') as $lead)
                    <div class="bg-blue-50 hover:bg-blue-100 transition duration-150 ease-in-out p-4 rounded shadow-sm border border-blue-100">
                        <h3 class="font-bold text-gray-800">{{ $lead->name }}</h3>
                        <p class="text-sm text-gray-600 mt-1 font-mono">{{ $lead->phone }}</p>
                        <span class="inline-block mt-3 px-2 py-1 bg-blue-200 text-blue-800 text-xs font-semibold rounded-full">Follow Up</span>
                    </div>
                    @endforeach
                    @if($leads->where('stage', 'Follow Up')->isEmpty())
                        <p class="text-sm text-gray-500 text-center py-4 italic">No leads here.</p>
                    @endif
                </div>
            </div>

            <!-- Payment Column -->
            <div class="flex-1 bg-white rounded-lg shadow p-4 border-t-4 border-yellow-500">
                <h2 class="text-lg font-semibold border-b pb-2 mb-4 text-yellow-600 flex justify-between items-center">
                    Payment
                    <span class="bg-yellow-100 text-yellow-800 text-xs py-1 px-2 rounded-full">{{ $leads->where('stage', 'Payment')->count() }}</span>
                </h2>
                <div class="space-y-4">
                    @foreach($leads->where('stage', 'Payment') as $lead)
                    <div class="bg-yellow-50 hover:bg-yellow-100 transition duration-150 ease-in-out p-4 rounded shadow-sm border border-yellow-100">
                        <h3 class="font-bold text-gray-800">{{ $lead->name }}</h3>
                        <p class="text-sm text-gray-600 mt-1 font-mono">{{ $lead->phone }}</p>
                        <span class="inline-block mt-3 px-2 py-1 bg-yellow-200 text-yellow-800 text-xs font-semibold rounded-full">Payment</span>
                    </div>
                    @endforeach
                    @if($leads->where('stage', 'Payment')->isEmpty())
                        <p class="text-sm text-gray-500 text-center py-4 italic">No leads here.</p>
                    @endif
                </div>
            </div>

            <!-- Closed Column -->
            <div class="flex-1 bg-white rounded-lg shadow p-4 border-t-4 border-green-500">
                <h2 class="text-lg font-semibold border-b pb-2 mb-4 text-green-600 flex justify-between items-center">
                    Closed
                    <span class="bg-green-100 text-green-800 text-xs py-1 px-2 rounded-full">{{ $leads->where('stage', 'Closed')->count() }}</span>
                </h2>
                <div class="space-y-4">
                    @foreach($leads->where('stage', 'Closed') as $lead)
                    <div class="bg-green-50 hover:bg-green-100 transition duration-150 ease-in-out p-4 rounded shadow-sm border border-green-100">
                        <h3 class="font-bold text-gray-800">{{ $lead->name }}</h3>
                        <p class="text-sm text-gray-600 mt-1 font-mono">{{ $lead->phone }}</p>
                        <span class="inline-block mt-3 px-2 py-1 bg-green-200 text-green-800 text-xs font-semibold rounded-full">Closed</span>
                    </div>
                    @endforeach
                    @if($leads->where('stage', 'Closed')->isEmpty())
                        <p class="text-sm text-gray-500 text-center py-4 italic">No leads here.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto-refresh the page every 5 seconds to update the UI
        setInterval(() => {
            window.location.reload();
        }, 5000);
    </script>
</body>
</html>
