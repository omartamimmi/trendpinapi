import AdminLayout from '@/Layouts/AdminLayout';
import Onboarding from '../Retailer/Onboarding';
import { router } from '@inertiajs/react';

interface EditOnboardingProps {
  onboarding: any;
  user?: any;
  currentStep: number;
  plans: any[];
  csrf_token?: string;
  existingPaymentMethods?: any[];
  existingBrands?: any[];
  existingSubscription?: any;
}

export default function EditOnboarding(props: EditOnboardingProps) {
  return (
    <AdminLayout>
      <div className="mb-6">
        <div className="flex items-center space-x-4">
          <button
            onClick={() => router.visit('/admin/onboarding-approvals')}
            className="p-2 hover:bg-gray-100 rounded-lg"
          >
            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 19l-7-7 7-7" />
            </svg>
          </button>
          <div>
            <h1 className="text-2xl font-bold text-gray-900">Edit Onboarding</h1>
            <p className="text-sm text-gray-600 mt-1">
              Editing onboarding for {props.user?.name || 'Retailer'}
            </p>
          </div>
        </div>
      </div>

      <div className="bg-white rounded-xl shadow-sm p-6">
        <Onboarding {...props} isAdminEdit={true} />
      </div>
    </AdminLayout>
  );
}
