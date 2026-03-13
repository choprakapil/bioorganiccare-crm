import { useGlobalSystem } from '../../context/GlobalSystemContext';

export default function PlanLimitBanner() {
  const { planLimitReached, resetPlanLimit } = useGlobalSystem();

  if (!planLimitReached) return null;

  return (
    <div className="bg-orange-500 text-white px-6 py-3 flex justify-between items-center">
      <span className="font-semibold">
        You have reached your plan limit. Upgrade to continue adding new records.
      </span>
      <button
        onClick={resetPlanLimit}
        className="bg-white text-orange-600 px-4 py-1 rounded-lg font-bold"
      >
        Dismiss
      </button>
    </div>
  );
}
