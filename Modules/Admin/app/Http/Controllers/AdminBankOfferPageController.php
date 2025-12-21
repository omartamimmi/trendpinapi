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
use Illuminate\Support\Facades\Auth;

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
