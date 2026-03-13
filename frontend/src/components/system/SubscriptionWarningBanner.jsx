import { useGlobalSystem } from '../../context/GlobalSystemContext';

export default function SubscriptionWarningBanner() {
  const { subscriptionWarning } = useGlobalSystem();

  if (!subscriptionWarning) return null;

  return (
    <div className="bg-yellow-400 text-slate-900 px-6 py-3 text-center font-semibold">
      {subscriptionWarning}
    </div>
  );
}
