import React from 'react';

// ConflictPreviewModal.jsx
export default function ConflictPreviewModal({ conflictData, onForce, onCancel }) {
    if (!conflictData) return null;

    return (
        <div className="fixed inset-0 bg-black/50 flex justify-center items-center z-50">
            <div className="bg-white p-6 rounded-[2.5rem] max-w-lg w-full">
                <h2 className="text-xl font-bold mb-4">Conflict Detected</h2>
                {conflictData.status === 'conflict_exact' && (
                    <div className="mb-4 text-red-600">
                        Exact match already exists (Global ID: {conflictData.global_id}). Promotion blocked.
                    </div>
                )}
                {conflictData.status === 'conflict_similar' && (
                    <div className="mb-4">
                        <p className="mb-2 text-amber-600">Similar items already exist:</p>
                        <ul className="list-disc pl-5 mb-4 max-h-40 overflow-y-auto bg-slate-50 p-3 rounded-xl border border-slate-200">
                            {conflictData.suggestions.map(s => (
                                <li key={s.id}>{s.item_name} (ID: {s.id})</li>
                            ))}
                        </ul>
                        <p className="text-sm text-gray-600">Do you want to force promote this item anyway?</p>
                    </div>
                )}
                <div className="flex justify-end gap-3 mt-6">
                    <button onClick={onCancel} className="px-5 py-2 hover:bg-slate-100 rounded-full text-slate-700 font-medium">Cancel</button>
                    {conflictData.status === 'conflict_similar' && (
                        <button onClick={onForce} className="px-5 py-2 bg-indigo-600 text-white rounded-full font-medium hover:bg-indigo-700 shadow-sm">Force Promote</button>
                    )}
                </div>
            </div>
        </div>
    );
}
