import { FaCheckCircle, FaClock, FaEnvelope } from "react-icons/fa";
import { Link } from "@inertiajs/react";

interface OnboardingPendingProps {
  onboarding: {
    id: number;
    user_id: number;
    current_step: string;
    status: string;
    approval_status: string;
    created_at: string;
    updated_at: string;
  };
  user: {
    id: number;
    name: string;
    email: string;
  };
}

export default function OnboardingPending({ onboarding, user }: OnboardingPendingProps) {
  return (
    <div className="min-h-screen bg-gradient-to-br from-pink-50 to-purple-50 flex items-center justify-center p-6">
      <div className="max-w-2xl w-full bg-white rounded-2xl shadow-xl p-8 md:p-12">
        <div className="text-center">
          {/* Icon */}
          <div className="flex justify-center mb-6">
            <div className="relative">
              <div className="w-24 h-24 bg-yellow-100 rounded-full flex items-center justify-center">
                <FaClock className="text-yellow-500 text-5xl" />
              </div>
              <div className="absolute -top-2 -right-2 w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                <FaCheckCircle className="text-white text-sm" />
              </div>
            </div>
          </div>

          {/* Title */}
          <h1 className="text-3xl font-bold text-gray-800 mb-4">
            Application Submitted Successfully!
          </h1>

          {/* Message */}
          <p className="text-lg text-gray-600 mb-8">
            Thank you for completing your retailer onboarding, <strong>{user.name}</strong>!
          </p>

          {/* Status Box */}
          <div className="bg-gradient-to-r from-yellow-50 to-orange-50 border-2 border-yellow-200 rounded-xl p-6 mb-8">
            <div className="flex items-center justify-center mb-4">
              <FaClock className="text-yellow-600 text-2xl mr-3" />
              <h2 className="text-xl font-semibold text-gray-800">Pending Admin Approval</h2>
            </div>
            <p className="text-gray-700 leading-relaxed">
              Your application is currently under review by our admin team.
              This process typically takes 1-3 business days. You'll receive an email
              notification at <span className="font-semibold text-pink-600">{user.email}</span> once
              your account has been approved.
            </p>
          </div>

          {/* Info Cards */}
          <div className="grid md:grid-cols-2 gap-4 mb-8">
            <div className="bg-gray-50 rounded-lg p-4 text-left">
              <h3 className="font-semibold text-gray-800 mb-2 flex items-center">
                <FaEnvelope className="mr-2 text-pink-500" />
                What's Next?
              </h3>
              <p className="text-sm text-gray-600">
                Once approved, you'll receive an email with instructions to access your retailer dashboard.
              </p>
            </div>
            <div className="bg-gray-50 rounded-lg p-4 text-left">
              <h3 className="font-semibold text-gray-800 mb-2 flex items-center">
                <FaClock className="mr-2 text-pink-500" />
                Review Time
              </h3>
              <p className="text-sm text-gray-600">
                Our team will review your application within 1-3 business days during working hours.
              </p>
            </div>
          </div>

          {/* Submission Details */}
          <div className="border-t pt-6">
            <p className="text-sm text-gray-500">
              Application ID: <span className="font-mono font-semibold">#{onboarding.id.toString().padStart(6, '0')}</span>
            </p>
            <p className="text-sm text-gray-500 mt-1">
              Submitted on: {new Date(onboarding.updated_at).toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
              })}
            </p>
          </div>

          {/* Logout Button */}
          <div className="mt-8">
            <Link
              href="/logout"
              method="post"
              as="button"
              className="inline-block px-8 py-3 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg transition-colors"
            >
              Logout
            </Link>
          </div>
        </div>
      </div>
    </div>
  );
}
