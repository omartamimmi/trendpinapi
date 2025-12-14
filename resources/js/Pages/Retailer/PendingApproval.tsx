import React from 'react';
import { Head, Link } from '@inertiajs/react';

interface Props {
  status: 'pending' | 'pending_approval' | 'approved' | 'changes_requested' | 'rejected';
  admin_notes?: string | null;
}

export default function PendingApproval({ status, admin_notes }: Props) {
  const getStatusDetails = () => {
    switch (status) {
      case 'pending_approval':
        return {
          icon: '⏳',
          title: 'Application Under Review',
          message: 'Your application is currently being reviewed by our admin team. This typically takes 1-3 business days.',
          color: 'text-yellow-600',
          bgColor: 'bg-yellow-50',
          borderColor: 'border-yellow-200',
        };
      case 'changes_requested':
        return {
          icon: '📝',
          title: 'Changes Requested',
          message: 'The admin has requested some changes to your application. Please review the notes below and update your information.',
          color: 'text-orange-600',
          bgColor: 'bg-orange-50',
          borderColor: 'border-orange-200',
        };
      case 'rejected':
        return {
          icon: '❌',
          title: 'Application Rejected',
          message: 'Unfortunately, your application has been rejected. Please see the admin notes below for more information.',
          color: 'text-red-600',
          bgColor: 'bg-red-50',
          borderColor: 'border-red-200',
        };
      default:
        return {
          icon: '🔍',
          title: 'Application Pending',
          message: 'Your application is pending review.',
          color: 'text-gray-600',
          bgColor: 'bg-gray-50',
          borderColor: 'border-gray-200',
        };
    }
  };

  const details = getStatusDetails();

  return (
    <>
      <Head title="Pending Approval" />

      <div className="min-h-screen bg-gray-50 flex items-center justify-center px-4 py-8">
        <div className="max-w-2xl w-full">
          {/* Status Card */}
          <div className={`bg-white rounded-lg shadow-lg border-2 ${details.borderColor} p-8`}>
            {/* Icon */}
            <div className="text-center mb-6">
              <span className="text-6xl">{details.icon}</span>
            </div>

            {/* Title */}
            <h1 className={`text-3xl font-bold text-center mb-4 ${details.color}`}>
              {details.title}
            </h1>

            {/* Message */}
            <p className="text-center text-gray-700 mb-6 text-lg">
              {details.message}
            </p>

            {/* Admin Notes */}
            {admin_notes && (
              <div className={`${details.bgColor} ${details.borderColor} border-l-4 p-4 mb-6 rounded`}>
                <h3 className={`font-semibold ${details.color} mb-2`}>
                  Admin Notes:
                </h3>
                <p className="text-gray-700 whitespace-pre-wrap">{admin_notes}</p>
              </div>
            )}

            {/* Action Buttons */}
            <div className="flex flex-col sm:flex-row gap-4 justify-center mt-8">
              <a
                href="mailto:support@trendpin.app"
                className="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg font-medium hover:bg-gray-300 transition-colors text-center"
              >
                Contact Support
              </a>

              <Link
                href="/logout"
                method="post"
                as="button"
                className="px-6 py-3 bg-white border-2 border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-50 transition-colors text-center"
              >
                Logout
              </Link>

              {(status === 'changes_requested' || status === 'rejected') && (
                <a
                  href="/retailer/onboarding?edit=1"
                  className="px-6 py-3 bg-[#E8347E] text-white rounded-lg font-medium hover:bg-[#c72a68] transition-colors text-center"
                >
                  Edit Application
                </a>
              )}
            </div>
          </div>

          {/* Info Box */}
          <div className="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div className="flex items-start">
              <span className="text-blue-500 text-2xl mr-3">ℹ️</span>
              <div>
                <h3 className="font-semibold text-blue-900 mb-1">What happens next?</h3>
                <p className="text-blue-800 text-sm">
                  Once your application is approved, you'll receive an email notification and will be able to access your retailer dashboard.
                  You can then start managing your brands, branches, and offers.
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </>
  );
}
