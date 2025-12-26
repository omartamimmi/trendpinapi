<?php

namespace Modules\Admin\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\BankOffer\app\Models\Bank;
use Modules\BankOffer\app\Models\CardType;
use Modules\BankOffer\app\Models\BankOffer;
use Modules\BankOffer\app\Models\BankOfferBrand;
use Modules\Business\app\Models\Brand;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminBankOfferPageController extends Controller
{
    // ==================== BANKS ====================

    public function banks(Request $request): Response
    {
        $search = $request->get('search');
        $status = $request->get('status');

        $query = Bank::with('logo')->withCount(['offers', 'cardTypes']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('name_ar', 'like', "%{$search}%");
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        $banks = $query->latest()->paginate(20);

        $stats = [
            'total' => Bank::count(),
            'active' => Bank::where('status', 'active')->count(),
            'cardTypes' => CardType::count(),
            'offers' => BankOffer::count(),
        ];

        return Inertia::render('Admin/BankOffer/Banks', [
            'banks' => $banks,
            'stats' => $stats,
            'filters' => [
                'search' => $search,
                'status' => $status,
            ],
        ]);
    }

    public function createBank(): Response
    {
        return Inertia::render('Admin/BankOffer/BankForm', [
            'bank' => null,
        ]);
    }

    public function storeBank(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'logo_id' => 'nullable|exists:media_files,id',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        Bank::create($validated);

        return redirect('/admin/bank-offer/banks')->with('success', 'Bank created successfully');
    }

    public function editBank(int $id): Response
    {
        $bank = Bank::with('logo')->findOrFail($id);

        return Inertia::render('Admin/BankOffer/BankForm', [
            'bank' => $bank,
        ]);
    }

    public function updateBank(Request $request, int $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'logo_id' => 'nullable|exists:media_files,id',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        Bank::findOrFail($id)->update($validated);

        return redirect('/admin/bank-offer/banks')->with('success', 'Bank updated successfully');
    }

    public function destroyBank(int $id)
    {
        Bank::findOrFail($id)->delete();

        return redirect()->back()->with('success', 'Bank deleted successfully');
    }

    // ==================== CARD TYPES ====================

    public function cardTypes(Request $request): Response
    {
        $search = $request->get('search');
        $status = $request->get('status');
        $bankId = $request->get('bank_id');
        $cardNetwork = $request->get('card_network');

        $query = CardType::with(['bank', 'logo']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('name_ar', 'like', "%{$search}%");
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($bankId) {
            $query->where('bank_id', $bankId);
        }

        if ($cardNetwork) {
            $query->where('card_network', $cardNetwork);
        }

        $cardTypes = $query->latest()->paginate(20);
        $banks = Bank::where('status', 'active')->get(['id', 'name']);

        $stats = [
            'total' => CardType::count(),
            'active' => CardType::where('status', 'active')->count(),
            'visa' => CardType::where('card_network', 'visa')->count(),
            'mastercard' => CardType::where('card_network', 'mastercard')->count(),
        ];

        return Inertia::render('Admin/BankOffer/CardTypes', [
            'cardTypes' => $cardTypes,
            'banks' => $banks,
            'stats' => $stats,
            'filters' => [
                'search' => $search,
                'status' => $status,
                'bank_id' => $bankId,
                'card_network' => $cardNetwork,
            ],
        ]);
    }

    public function createCardType(): Response
    {
        $banks = Bank::where('status', 'active')->get(['id', 'name']);

        return Inertia::render('Admin/BankOffer/CardTypeForm', [
            'cardType' => null,
            'banks' => $banks,
        ]);
    }

    public function storeCardType(Request $request)
    {
        $validated = $request->validate([
            'bank_id' => 'nullable|exists:banks,id',
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'logo_id' => 'nullable|exists:media_files,id',
            'card_network' => 'required|in:visa,mastercard,amex,other',
            'bin_prefixes' => 'nullable|array',
            'bin_prefixes.*' => 'string|min:4|max:8|regex:/^\d+$/',
            'card_color' => 'nullable|string|max:100',
            'status' => 'required|in:active,inactive',
        ]);

        CardType::create($validated);

        return redirect('/admin/bank-offer/card-types')->with('success', 'Card type created successfully');
    }

    public function editCardType(int $id): Response
    {
        $cardType = CardType::with('logo')->findOrFail($id);
        $banks = Bank::where('status', 'active')->get(['id', 'name']);

        return Inertia::render('Admin/BankOffer/CardTypeForm', [
            'cardType' => $cardType,
            'banks' => $banks,
        ]);
    }

    public function updateCardType(Request $request, int $id)
    {
        $validated = $request->validate([
            'bank_id' => 'nullable|exists:banks,id',
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'logo_id' => 'nullable|exists:media_files,id',
            'card_network' => 'required|in:visa,mastercard,amex,other',
            'bin_prefixes' => 'nullable|array',
            'bin_prefixes.*' => 'string|min:4|max:8|regex:/^\d+$/',
            'card_color' => 'nullable|string|max:100',
            'status' => 'required|in:active,inactive',
        ]);

        CardType::findOrFail($id)->update($validated);

        return redirect('/admin/bank-offer/card-types')->with('success', 'Card type updated successfully');
    }

    public function destroyCardType(int $id)
    {
        CardType::findOrFail($id)->delete();

        return redirect()->back()->with('success', 'Card type deleted successfully');
    }

    // ==================== BANK OFFERS ====================

    public function createOffer(): Response
    {
        $banks = Bank::where('status', 'active')->with('logo')->get(['id', 'name', 'name_ar', 'logo_id']);
        $cardTypes = CardType::where('status', 'active')->get(['id', 'bank_id', 'name', 'name_ar', 'card_network']);
        $brands = Brand::where('status', 'publish')
            ->with(['branches' => fn($q) => $q->where('status', 'publish')->select('id', 'brand_id', 'name', 'location', 'is_main')])
            ->get(['id', 'name', 'title', 'title_ar']);

        return Inertia::render('Admin/BankOffer/BankOfferForm', [
            'offer' => null,
            'banks' => $banks,
            'cardTypes' => $cardTypes,
            'brands' => $brands,
        ]);
    }

    public function storeOffer(Request $request)
    {
        $validated = $request->validate([
            'bank_id' => 'required|exists:banks,id',
            'card_type_id' => 'nullable|exists:card_types,id',
            'title' => 'required|string|max:255',
            'title_ar' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'offer_type' => 'required|in:percentage,fixed,cashback',
            'offer_value' => 'required|numeric|min:0',
            'min_purchase_amount' => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'terms' => 'nullable|string',
            'terms_ar' => 'nullable|string',
            'redemption_type' => 'nullable|in:show_only,qr_code,in_app',
            'max_claims' => 'nullable|integer|min:0',
            'status' => 'required|in:draft,pending,active,paused',
            'brand_assignments' => 'nullable|array',
            'brand_assignments.*.brand_id' => 'required|exists:brands,id',
            'brand_assignments.*.all_branches' => 'boolean',
            'brand_assignments.*.branch_ids' => 'nullable|array',
        ]);

        $brandAssignments = $validated['brand_assignments'] ?? [];
        unset($validated['brand_assignments']);

        $validated['created_by'] = Auth::id();

        // Auto-approve if admin sets to active
        if ($validated['status'] === 'active') {
            $validated['approved_by'] = Auth::id();
            $validated['approved_at'] = now();
        }

        DB::transaction(function () use ($validated, $brandAssignments) {
            $offer = BankOffer::create($validated);

            // Create brand assignments
            foreach ($brandAssignments as $assignment) {
                BankOfferBrand::create([
                    'bank_offer_id' => $offer->id,
                    'brand_id' => $assignment['brand_id'],
                    'all_branches' => $assignment['all_branches'] ?? true,
                    'branch_ids' => $assignment['branch_ids'] ?? null,
                    'status' => 'approved', // Admin-created assignments are auto-approved
                    'approved_at' => now(),
                    'approved_by' => Auth::id(),
                ]);
            }
        });

        return redirect('/admin/bank-offer/offers')->with('success', 'Bank offer created successfully');
    }

    public function editOffer(int $id): Response
    {
        $offer = BankOffer::with(['bank', 'cardType', 'participatingBrands.brand'])->findOrFail($id);
        $banks = Bank::where('status', 'active')->with('logo')->get(['id', 'name', 'name_ar', 'logo_id']);
        $cardTypes = CardType::where('status', 'active')->get(['id', 'bank_id', 'name', 'name_ar', 'card_network']);
        $brands = Brand::where('status', 'publish')
            ->with(['branches' => fn($q) => $q->where('status', 'publish')->select('id', 'brand_id', 'name', 'location', 'is_main')])
            ->get(['id', 'name', 'title', 'title_ar']);

        // Transform existing assignments for the form
        $existingAssignments = $offer->participatingBrands->map(fn($p) => [
            'brand_id' => $p->brand_id,
            'all_branches' => $p->all_branches,
            'branch_ids' => $p->branch_ids ?? [],
            'status' => $p->status,
        ])->toArray();

        return Inertia::render('Admin/BankOffer/BankOfferForm', [
            'offer' => $offer,
            'banks' => $banks,
            'cardTypes' => $cardTypes,
            'brands' => $brands,
            'existingAssignments' => $existingAssignments,
        ]);
    }

    public function updateOffer(Request $request, int $id)
    {
        $validated = $request->validate([
            'bank_id' => 'required|exists:banks,id',
            'card_type_id' => 'nullable|exists:card_types,id',
            'title' => 'required|string|max:255',
            'title_ar' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'offer_type' => 'required|in:percentage,fixed,cashback',
            'offer_value' => 'required|numeric|min:0',
            'min_purchase_amount' => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'terms' => 'nullable|string',
            'terms_ar' => 'nullable|string',
            'redemption_type' => 'nullable|in:show_only,qr_code,in_app',
            'max_claims' => 'nullable|integer|min:0',
            'status' => 'required|in:draft,pending,active,paused,expired',
            'brand_assignments' => 'nullable|array',
            'brand_assignments.*.brand_id' => 'required|exists:brands,id',
            'brand_assignments.*.all_branches' => 'boolean',
            'brand_assignments.*.branch_ids' => 'nullable|array',
        ]);

        $brandAssignments = $validated['brand_assignments'] ?? [];
        unset($validated['brand_assignments']);

        $offer = BankOffer::findOrFail($id);

        // Track if status changed to active for approval tracking
        if ($validated['status'] === 'active' && $offer->status !== 'active') {
            $validated['approved_by'] = Auth::id();
            $validated['approved_at'] = now();
        }

        DB::transaction(function () use ($offer, $validated, $brandAssignments) {
            $offer->update($validated);

            // Get existing brand IDs
            $existingBrandIds = $offer->participatingBrands->pluck('brand_id')->toArray();
            $newBrandIds = array_column($brandAssignments, 'brand_id');

            // Remove brands that are no longer assigned
            $brandsToRemove = array_diff($existingBrandIds, $newBrandIds);
            if (!empty($brandsToRemove)) {
                BankOfferBrand::where('bank_offer_id', $offer->id)
                    ->whereIn('brand_id', $brandsToRemove)
                    ->delete();
            }

            // Update or create brand assignments
            foreach ($brandAssignments as $assignment) {
                BankOfferBrand::updateOrCreate(
                    [
                        'bank_offer_id' => $offer->id,
                        'brand_id' => $assignment['brand_id'],
                    ],
                    [
                        'all_branches' => $assignment['all_branches'] ?? true,
                        'branch_ids' => $assignment['branch_ids'] ?? null,
                        'status' => 'approved',
                        'approved_at' => now(),
                        'approved_by' => Auth::id(),
                    ]
                );
            }
        });

        return redirect('/admin/bank-offer/offers')->with('success', 'Bank offer updated successfully');
    }

    public function destroyOffer(int $id)
    {
        BankOffer::findOrFail($id)->delete();

        return redirect()->back()->with('success', 'Bank offer deleted successfully');
    }

    public function offers(Request $request): Response
    {
        $search = $request->get('search');
        $status = $request->get('status');
        $bankId = $request->get('bank_id');
        $offerType = $request->get('offer_type');

        $query = BankOffer::with(['bank', 'cardType'])
            ->withCount('participatingBrands');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('title_ar', 'like', "%{$search}%");
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($bankId) {
            $query->where('bank_id', $bankId);
        }

        if ($offerType) {
            $query->where('offer_type', $offerType);
        }

        $offers = $query->latest()->paginate(20);
        $banks = Bank::where('status', 'active')->get(['id', 'name']);

        $stats = [
            'total' => BankOffer::count(),
            'pending' => BankOffer::where('status', 'pending')->count(),
            'active' => BankOffer::where('status', 'active')->count(),
            'retailers' => BankOfferBrand::where('status', 'approved')->distinct('brand_id')->count('brand_id'),
            'claims' => BankOffer::sum('total_claims'),
        ];

        return Inertia::render('Admin/BankOffer/Offers', [
            'offers' => $offers,
            'banks' => $banks,
            'stats' => $stats,
            'filters' => [
                'search' => $search,
                'status' => $status,
                'bank_id' => $bankId,
                'offer_type' => $offerType,
            ],
        ]);
    }

    public function showOffer(int $id): Response
    {
        $offer = BankOffer::with(['bank', 'cardType', 'participatingBrands.brand', 'creator', 'approver'])
            ->findOrFail($id);

        return Inertia::render('Admin/BankOffer/OfferDetail', [
            'offer' => $offer,
        ]);
    }

    public function approveOffer(int $id)
    {
        $offer = BankOffer::findOrFail($id);
        $offer->update([
            'status' => 'active',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Offer approved successfully');
    }

    public function rejectOffer(int $id)
    {
        $offer = BankOffer::findOrFail($id);
        $offer->update(['status' => 'rejected']);

        return redirect()->back()->with('success', 'Offer rejected');
    }

    public function updateOfferStatus(Request $request, int $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:draft,pending,active,paused,expired',
        ]);

        BankOffer::findOrFail($id)->update($validated);

        return redirect()->back()->with('success', 'Offer status updated');
    }

    // ==================== PARTICIPATION REQUESTS ====================

    public function requests(Request $request): Response
    {
        $status = $request->get('status');
        $bankOfferId = $request->get('bank_offer_id');

        $query = BankOfferBrand::with(['bankOffer.bank', 'brand', 'approver']);

        if ($status) {
            $query->where('status', $status);
        }

        if ($bankOfferId) {
            $query->where('bank_offer_id', $bankOfferId);
        }

        $requests = $query->latest()->paginate(20);
        $offers = BankOffer::where('status', 'active')->get(['id', 'title']);

        $stats = [
            'total' => BankOfferBrand::count(),
            'pending' => BankOfferBrand::where('status', 'pending')->count(),
            'approved' => BankOfferBrand::where('status', 'approved')->count(),
            'rejected' => BankOfferBrand::where('status', 'rejected')->count(),
        ];

        return Inertia::render('Admin/BankOffer/Requests', [
            'requests' => $requests,
            'offers' => $offers,
            'stats' => $stats,
            'filters' => [
                'status' => $status,
                'bank_offer_id' => $bankOfferId,
            ],
        ]);
    }

    public function approveRequest(int $id)
    {
        $request = BankOfferBrand::findOrFail($id);
        $request->approve(Auth::id());

        return redirect()->back()->with('success', 'Participation approved');
    }

    public function rejectRequest(int $id)
    {
        $request = BankOfferBrand::findOrFail($id);
        $request->reject();

        return redirect()->back()->with('success', 'Participation rejected');
    }
}
