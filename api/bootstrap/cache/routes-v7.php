<?php

app('router')->setCompiledRoutes(
    array (
  'compiled' => 
  array (
    0 => false,
    1 => 
    array (
      '/horizon/api/stats' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'horizon.stats.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/horizon/api/workload' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'horizon.workload.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/horizon/api/masters' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'horizon.masters.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/horizon/api/monitoring' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'horizon.monitoring.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'horizon.monitoring.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/horizon/api/metrics/jobs' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'horizon.jobs-metrics.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/horizon/api/metrics/queues' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'horizon.queues-metrics.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/horizon/api/batches' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'horizon.jobs-batches.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/horizon/api/jobs/pending' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'horizon.pending-jobs.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/horizon/api/jobs/completed' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'horizon.completed-jobs.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/horizon/api/jobs/silenced' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'horizon.silenced-jobs.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/horizon/api/jobs/failed' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'horizon.failed-jobs.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/sanctum/csrf-cookie' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'sanctum.csrf-cookie',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/broadcasting/auth' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::9MDsbjPI9to9733X',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'POST' => 1,
            'HEAD' => 2,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/login' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::idBzzapa0f6XGA0J',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/landing-enquiry' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::CiMDqsl5aYlgDao3',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/me' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::5hPXfISA53JXYAN4',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/logout' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::4eJmwtv3tIwit04x',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/settings/profile' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::JBiP7sCFw0Sim0mP',
          ),
          1 => NULL,
          2 => 
          array (
            'PATCH' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/clinical-catalog' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::Xi8IASvdukqtDwrT',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/hybrid-suggestions/services' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::dQsRL0iQrNUUle25',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/hybrid-suggestions/medicines' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::Gt5RyaRBDD0KwqIl',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/hybrid-suggestions/master-medicines' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::Vw9SoQltVoJHLM3j',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/hybrid-promotions/bulk' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::ejpXSuNHADNc1REl',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/hybrid-promotions/pending' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::VYFLuIJ3GMPm03np',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/delete/requests' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::rC2srToaeitykJtZ',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/governance/health' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::dwCYGai2PBZDPQdE',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/governance/repair/duplicate-services' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::nhbTl8bRgiuXVqBK',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/governance/repair/duplicate-medicines' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::OYsU2ygL4SrOOxhg',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/governance/repair/inventory-batches' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::enBNfUHo27SPssEP',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/governance/repair/orphan-treatments' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::srPbamQwNdB5bkyq',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/governance/repair/negative-inventory' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::OCTRWEPBLbQIcTAZ',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/governance/repair/medicine-drift' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::dt7CZIUJgRDwlHsI',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/governance/repair/floating-ledger' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::ChnBHfn53HmlQlf5',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/patients' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'patients.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'patients.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/appointments' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'appointments.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'appointments.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/invoices' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'billing.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'billing.invoices.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/expenses/summary' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'expenses.summary',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/expenses' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'expenses.expenses.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'expenses.expenses.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/clinical-catalog' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'clinical-catalog.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'clinical-catalog.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/clinical-catalog/settings' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'clinical-catalog.settings',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/treatments' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'clinical-catalog.treatments.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'clinical-catalog.treatments.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/pharmacy/catalog' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'inventory.catalog',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/inventory/search-medicines' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'inventory.',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/inventory/analytics' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'inventory.analytics',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/inventory' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'inventory.generated::o5C5KYQqeHrWtvC9',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'inventory.inventory.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/staff' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'staff.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'staff.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/staff/me' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::4fINwN72yvetFDm0',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/settings/branding' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::NVxzPwFc97JclDty',
          ),
          1 => NULL,
          2 => 
          array (
            'PATCH' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/settings/logo' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::UCCRFnBASFUUuORh',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/settings' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::akZKkzRTRfGKurPS',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/growth/insights' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'insights.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/finance/summary' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'expenses.finance_summary',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/finance/revenue-trend' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'expenses.revenue_trend',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/system/integrity' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'expenses.integrity',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/audit-logs' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'audit_logs.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/subscription/usage' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::SZTPFKFhzoQzobmy',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/subscription/me' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::sVfcu6zb5ze5CSWd',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/notifications' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::rnKXPFpZP1xyXjLD',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/notifications/read-all' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::DbTiBhMqkqlKgeZ1',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/stop-impersonation' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::uZ1W8qUHXPK7qE1q',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/dashboard/stats' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::VNqUN8baJ47qKGut',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/doctors' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::CICiSb5QEmn6R6Tn',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'generated::UuXHoOU1rSreXOip',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/doctors/reference-data' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::SKx3Bg2PpnDEOOm0',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/plans' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::DScO5CeVdK4njjlH',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'generated::gZv3JkWAB0MRNDTY',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/specialties/archived' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::h80eZqXyOG0WJo8c',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/services/archived' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::QShSuzyV28fLdjWO',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/medicines/archived' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::Ka2FhvCusJjVJ3uY',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/specialties' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'specialties.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'specialties.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/clinical-catalog/import' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::UufYrM37braLBoY5',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/pharmacy-catalog/import' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::JQiANljufk70lfHg',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/system-settings' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::S9M3ExCOhhxYP5ia',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/service-submissions' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::yYr5Kp9Bdv5hyd6g',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/recycle-bin/doctors' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::0FJhq5nWUGRRifA4',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/enquiries' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::83MFkglBzAcwLl95',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/modules' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::c7bL5tz9cc3lVFC3',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/subscriptions' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::Z3lxBmlsPOwLbpn6',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/system-reset' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::xkv5x4lweNP7ctGg',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/up' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::yfD0dgaMguHunPal',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::MzzVbpH3F8EdaeKR',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/health' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::aZDF0KhAMcA3AqcQ',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/status' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::VXgueQIaXWscpffp',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/metrics' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::Fm3clMLMTcai6NUh',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/monitor' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::bjjiggf0OHqIJFqh',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/system' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::rNHCyuHiBPK9L6bc',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
    ),
    2 => 
    array (
      0 => '{^(?|/horizon(?|/api/(?|m(?|onitoring/(?|([^/]++)(*:51)|(.*)(*:62))|etrics/(?|jobs/([^/]++)(*:93)|queues/([^/]++)(*:115)))|batches/(?|([^/]++)(*:144)|retry/([^/]++)(*:166))|jobs/(?|failed/([^/]++)(*:198)|retry/([^/]++)(*:220)|([^/]++)(*:236)))|(?:/((?:.*)))?(*:260))|/p(?|ublic/invoices/([^/]++)(?|(*:300)|/pdf(*:312))|atients/([^/]++)(?|/(?|full(*:348)|treatments(*:366))|(*:375)))|/a(?|dmin/(?|hybrid\\-promotions/(?|service/([^/]++)(*:436)|medicine/([^/]++)(*:461)|approve/([^/]++)(*:485)|reject/([^/]++)(*:508))|d(?|elete/(?|requests/([^/]++)/(?|drift\\-check(*:563)|approve(*:578)|reject(*:592)|execute(*:607))|([^/]++)/(?|([^/]++)/(?|re(?|quest\\-cascade(*:659)|store(*:672))|summary(*:688)|archive(*:703)|force(?|(*:719)|\\-cascade(*:736))|cascade\\-preview(*:761))|bulk(*:774)))|octors/([^/]++)(?|/(?|toggle\\-active(*:820)|reset\\-password(*:843)|logs(*:855))|(*:864)))|impersonate/([^/]++)(*:894)|p(?|lans/([^/]++)(*:919)|harmacy\\-categories/([^/]++)(*:955))|s(?|pecialties/([^/]++)(?|(*:990)|/(?|cat(?|alog(*:1012)|egories(*:1028))|services(*:1046)|pharmacy\\-cat(?|alog(*:1075)|egories(*:1091))|medicines(*:1110)))|ervice(?|s/([^/]++)(*:1140)|\\-submissions/([^/]++)/(?|approve(*:1182)|reject(*:1197)))|ystem\\-settings/([^/]++)(*:1232)|ubscriptions/([^/]++)(?|(*:1265)|/(?|re(?|new(*:1286)|start(*:1300))|cancel(*:1316)|lifetime(*:1333)|plan(*:1346))))|categories/([^/]++)(*:1377)|medicines/([^/]++)(*:1404))|ppointments/([^/]++)(?|(*:1437)))|/inv(?|oices/([^/]++)(?|(*:1472)|/(?|pdf(*:1488)|finalize(*:1505)|status(*:1520)|app(?|ly\\-payment(*:1546)|rove\\-reallocation(*:1573)))|(*:1584))|entory/([^/]++)(?|/(?|replenish(*:1625)|adjust(*:1640))|(*:1650)))|/expenses/([^/]++)(?|(*:1682))|/treatments/([^/]++)(?|(*:1715))|/st(?|aff/([^/]++)(?|/activity(*:1755)|(*:1764))|orage/(.*)(*:1784))|/notifications/([^/]++)/read(*:1822))/?$}sDu',
    ),
    3 => 
    array (
      51 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'horizon.monitoring-tag.paginate',
          ),
          1 => 
          array (
            0 => 'tag',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      62 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'horizon.monitoring-tag.destroy',
          ),
          1 => 
          array (
            0 => 'tag',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      93 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'horizon.jobs-metrics.show',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      115 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'horizon.queues-metrics.show',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      144 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'horizon.jobs-batches.show',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      166 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'horizon.jobs-batches.retry',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      198 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'horizon.failed-jobs.show',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      220 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'horizon.retry-jobs.show',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      236 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'horizon.jobs.show',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      260 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'horizon.index',
            'view' => NULL,
          ),
          1 => 
          array (
            0 => 'view',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      300 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::E4C5scVTtlikZtJl',
          ),
          1 => 
          array (
            0 => 'uuid',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      312 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::Th3ptCAx9v5fmGPj',
          ),
          1 => 
          array (
            0 => 'uuid',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      348 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'patients.full',
          ),
          1 => 
          array (
            0 => 'patient',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      366 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'clinical-catalog.treatments.by_patient',
          ),
          1 => 
          array (
            0 => 'patient',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      375 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'patients.show',
          ),
          1 => 
          array (
            0 => 'patient',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'patients.update',
          ),
          1 => 
          array (
            0 => 'patient',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      436 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::eqQyAVCSsDUB6Ksg',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      461 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::7tNcfv8KjDBFVocH',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      485 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::PUOjLwYLNzgXuglx',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      508 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::Ispfgzgs9lxRpx1c',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      563 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::Un5fnLDOr5QRnJoz',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      578 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::NWXLR0aYz88Cpqy9',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      592 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::3xWGJWFUjTPoleqq',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      607 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::tVI9oozc5K8jYkfz',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      659 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::zRnby8Qh1HhjpPqA',
          ),
          1 => 
          array (
            0 => 'entity',
            1 => 'id',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      672 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::lERCwsdlqnNoNUUZ',
          ),
          1 => 
          array (
            0 => 'entity',
            1 => 'id',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      688 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::dVbNWAVXX9coVi11',
          ),
          1 => 
          array (
            0 => 'entity',
            1 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      703 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::aNwGKixYX9nBnmcJ',
          ),
          1 => 
          array (
            0 => 'entity',
            1 => 'id',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      719 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::bIT9uT8MtBHAJs8s',
          ),
          1 => 
          array (
            0 => 'entity',
            1 => 'id',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      736 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::CSMDp2oBI789U285',
          ),
          1 => 
          array (
            0 => 'entity',
            1 => 'id',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      761 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::BWokYKzn7reGjKjw',
          ),
          1 => 
          array (
            0 => 'entity',
            1 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      774 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::v9ANn8Doq45EX0vs',
          ),
          1 => 
          array (
            0 => 'entity',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      820 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::8mWVs5Pywvybg8Gp',
          ),
          1 => 
          array (
            0 => 'user',
          ),
          2 => 
          array (
            'PATCH' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      843 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::yYgByd0ZvZ4AxMhK',
          ),
          1 => 
          array (
            0 => 'user',
          ),
          2 => 
          array (
            'PATCH' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      855 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::mQaQW8y2C3UtCBP1',
          ),
          1 => 
          array (
            0 => 'user',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      864 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::fZ6Jgti3gJjDfMoJ',
          ),
          1 => 
          array (
            0 => 'user',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      894 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::V3LDFoNziJZ021f1',
          ),
          1 => 
          array (
            0 => 'doctor',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      919 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::ne5pslnQrv9ytYSs',
          ),
          1 => 
          array (
            0 => 'plan',
          ),
          2 => 
          array (
            'PATCH' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      955 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::iwWG4A6BXq53H2zH',
          ),
          1 => 
          array (
            0 => 'category',
          ),
          2 => 
          array (
            'PUT' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      990 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'specialties.show',
          ),
          1 => 
          array (
            0 => 'specialty',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'specialties.update',
          ),
          1 => 
          array (
            0 => 'specialty',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        2 => 
        array (
          0 => 
          array (
            '_route' => 'specialties.destroy',
          ),
          1 => 
          array (
            0 => 'specialty',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1012 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::lOZJXsnQkb3AK401',
          ),
          1 => 
          array (
            0 => 'specialty',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1028 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::V9gqHT6FkRFNLgTy',
          ),
          1 => 
          array (
            0 => 'specialty',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1046 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::xiPWJ60OQ3UG853r',
          ),
          1 => 
          array (
            0 => 'specialty',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1075 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::YSqXv5sx0mFAafro',
          ),
          1 => 
          array (
            0 => 'specialty',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1091 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::P9me0azdR9pPvRfG',
          ),
          1 => 
          array (
            0 => 'specialty',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1110 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::MBaSk4UihkaEMCof',
          ),
          1 => 
          array (
            0 => 'specialty',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1140 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::TLwXssY8Ef9ksxpK',
          ),
          1 => 
          array (
            0 => 'item',
          ),
          2 => 
          array (
            'PUT' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1182 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::QZtZzIjAu8ZpEjVx',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1197 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::WHpWjydUniEcT5RN',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1232 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::nbA2iNfKBYhMIubh',
          ),
          1 => 
          array (
            0 => 'key',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1265 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::1yDYUotXmvsU7GRT',
          ),
          1 => 
          array (
            0 => 'doctor',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1286 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::Dkyi5TqeKzKUfZaa',
          ),
          1 => 
          array (
            0 => 'doctor',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1300 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::2sj9MWwsX7Opxiy0',
          ),
          1 => 
          array (
            0 => 'doctor',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1316 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::XuEOVVsRMbDcZZLa',
          ),
          1 => 
          array (
            0 => 'doctor',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1333 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::dlzzabQo9JV9gy2i',
          ),
          1 => 
          array (
            0 => 'doctor',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1346 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::13TeNiQpATFZZdD8',
          ),
          1 => 
          array (
            0 => 'doctor',
          ),
          2 => 
          array (
            'PATCH' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1377 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::3HK3fKyY2QkNqAmY',
          ),
          1 => 
          array (
            0 => 'category',
          ),
          2 => 
          array (
            'PUT' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1404 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::OqmnxJGpOzaZld0b',
          ),
          1 => 
          array (
            0 => 'medicine',
          ),
          2 => 
          array (
            'PUT' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1437 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'appointments.show',
          ),
          1 => 
          array (
            0 => 'appointment',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'appointments.update',
          ),
          1 => 
          array (
            0 => 'appointment',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        2 => 
        array (
          0 => 
          array (
            '_route' => 'appointments.destroy',
          ),
          1 => 
          array (
            0 => 'appointment',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1472 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'billing.show',
          ),
          1 => 
          array (
            0 => 'invoice',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1488 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'billing.pdf',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1505 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'billing.invoices.finalize',
          ),
          1 => 
          array (
            0 => 'invoice',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1520 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'billing.invoices.status',
          ),
          1 => 
          array (
            0 => 'invoice',
          ),
          2 => 
          array (
            'PATCH' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1546 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'billing.invoices.apply_payment',
          ),
          1 => 
          array (
            0 => 'invoice',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1573 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'billing.',
          ),
          1 => 
          array (
            0 => 'invoice',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1584 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'billing.invoices.update',
          ),
          1 => 
          array (
            0 => 'invoice',
          ),
          2 => 
          array (
            'PUT' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1625 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'inventory.replenish',
          ),
          1 => 
          array (
            0 => 'inventory',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1640 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'inventory.adjust',
          ),
          1 => 
          array (
            0 => 'inventory',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1650 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'inventory.generated::Ruf6EWRQkUk2jC6F',
          ),
          1 => 
          array (
            0 => 'inventory',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'inventory.inventory.show',
          ),
          1 => 
          array (
            0 => 'inventory',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1682 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'expenses.expenses.show',
          ),
          1 => 
          array (
            0 => 'expense',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'expenses.expenses.update',
          ),
          1 => 
          array (
            0 => 'expense',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1715 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'clinical-catalog.treatments.update',
          ),
          1 => 
          array (
            0 => 'treatment',
          ),
          2 => 
          array (
            'PUT' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'clinical-catalog.treatments.show',
          ),
          1 => 
          array (
            0 => 'treatment',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1755 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::fFOOM4YF0h7wfC0O',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1764 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::Uv3IG0yzfKE7ej2Y',
          ),
          1 => 
          array (
            0 => 'staff',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'staff.show',
          ),
          1 => 
          array (
            0 => 'staff',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1784 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'storage.local',
          ),
          1 => 
          array (
            0 => 'path',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1822 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::mTuHNNR5NAAG7A0l',
          ),
          1 => 
          array (
            0 => 'notification',
          ),
          2 => 
          array (
            'PATCH' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => NULL,
          1 => NULL,
          2 => NULL,
          3 => NULL,
          4 => false,
          5 => false,
          6 => 0,
        ),
      ),
    ),
    4 => NULL,
  ),
  'attributes' => 
  array (
    'horizon.stats.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'horizon/api/stats',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 'horizon',
        'uses' => 'Laravel\\Horizon\\Http\\Controllers\\DashboardStatsController@index',
        'controller' => 'Laravel\\Horizon\\Http\\Controllers\\DashboardStatsController@index',
        'namespace' => 'Laravel\\Horizon\\Http\\Controllers',
        'prefix' => 'horizon/api',
        'where' => 
        array (
        ),
        'as' => 'horizon.stats.index',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'horizon.workload.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'horizon/api/workload',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 'horizon',
        'uses' => 'Laravel\\Horizon\\Http\\Controllers\\WorkloadController@index',
        'controller' => 'Laravel\\Horizon\\Http\\Controllers\\WorkloadController@index',
        'namespace' => 'Laravel\\Horizon\\Http\\Controllers',
        'prefix' => 'horizon/api',
        'where' => 
        array (
        ),
        'as' => 'horizon.workload.index',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'horizon.masters.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'horizon/api/masters',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 'horizon',
        'uses' => 'Laravel\\Horizon\\Http\\Controllers\\MasterSupervisorController@index',
        'controller' => 'Laravel\\Horizon\\Http\\Controllers\\MasterSupervisorController@index',
        'namespace' => 'Laravel\\Horizon\\Http\\Controllers',
        'prefix' => 'horizon/api',
        'where' => 
        array (
        ),
        'as' => 'horizon.masters.index',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'horizon.monitoring.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'horizon/api/monitoring',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 'horizon',
        'uses' => 'Laravel\\Horizon\\Http\\Controllers\\MonitoringController@index',
        'controller' => 'Laravel\\Horizon\\Http\\Controllers\\MonitoringController@index',
        'namespace' => 'Laravel\\Horizon\\Http\\Controllers',
        'prefix' => 'horizon/api',
        'where' => 
        array (
        ),
        'as' => 'horizon.monitoring.index',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'horizon.monitoring.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'horizon/api/monitoring',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 'horizon',
        'uses' => 'Laravel\\Horizon\\Http\\Controllers\\MonitoringController@store',
        'controller' => 'Laravel\\Horizon\\Http\\Controllers\\MonitoringController@store',
        'namespace' => 'Laravel\\Horizon\\Http\\Controllers',
        'prefix' => 'horizon/api',
        'where' => 
        array (
        ),
        'as' => 'horizon.monitoring.store',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'horizon.monitoring-tag.paginate' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'horizon/api/monitoring/{tag}',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 'horizon',
        'uses' => 'Laravel\\Horizon\\Http\\Controllers\\MonitoringController@paginate',
        'controller' => 'Laravel\\Horizon\\Http\\Controllers\\MonitoringController@paginate',
        'namespace' => 'Laravel\\Horizon\\Http\\Controllers',
        'prefix' => 'horizon/api',
        'where' => 
        array (
        ),
        'as' => 'horizon.monitoring-tag.paginate',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'horizon.monitoring-tag.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'horizon/api/monitoring/{tag}',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 'horizon',
        'uses' => 'Laravel\\Horizon\\Http\\Controllers\\MonitoringController@destroy',
        'controller' => 'Laravel\\Horizon\\Http\\Controllers\\MonitoringController@destroy',
        'namespace' => 'Laravel\\Horizon\\Http\\Controllers',
        'prefix' => 'horizon/api',
        'where' => 
        array (
        ),
        'as' => 'horizon.monitoring-tag.destroy',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
        'tag' => '.*',
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'horizon.jobs-metrics.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'horizon/api/metrics/jobs',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 'horizon',
        'uses' => 'Laravel\\Horizon\\Http\\Controllers\\JobMetricsController@index',
        'controller' => 'Laravel\\Horizon\\Http\\Controllers\\JobMetricsController@index',
        'namespace' => 'Laravel\\Horizon\\Http\\Controllers',
        'prefix' => 'horizon/api',
        'where' => 
        array (
        ),
        'as' => 'horizon.jobs-metrics.index',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'horizon.jobs-metrics.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'horizon/api/metrics/jobs/{id}',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 'horizon',
        'uses' => 'Laravel\\Horizon\\Http\\Controllers\\JobMetricsController@show',
        'controller' => 'Laravel\\Horizon\\Http\\Controllers\\JobMetricsController@show',
        'namespace' => 'Laravel\\Horizon\\Http\\Controllers',
        'prefix' => 'horizon/api',
        'where' => 
        array (
        ),
        'as' => 'horizon.jobs-metrics.show',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'horizon.queues-metrics.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'horizon/api/metrics/queues',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 'horizon',
        'uses' => 'Laravel\\Horizon\\Http\\Controllers\\QueueMetricsController@index',
        'controller' => 'Laravel\\Horizon\\Http\\Controllers\\QueueMetricsController@index',
        'namespace' => 'Laravel\\Horizon\\Http\\Controllers',
        'prefix' => 'horizon/api',
        'where' => 
        array (
        ),
        'as' => 'horizon.queues-metrics.index',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'horizon.queues-metrics.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'horizon/api/metrics/queues/{id}',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 'horizon',
        'uses' => 'Laravel\\Horizon\\Http\\Controllers\\QueueMetricsController@show',
        'controller' => 'Laravel\\Horizon\\Http\\Controllers\\QueueMetricsController@show',
        'namespace' => 'Laravel\\Horizon\\Http\\Controllers',
        'prefix' => 'horizon/api',
        'where' => 
        array (
        ),
        'as' => 'horizon.queues-metrics.show',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'horizon.jobs-batches.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'horizon/api/batches',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 'horizon',
        'uses' => 'Laravel\\Horizon\\Http\\Controllers\\BatchesController@index',
        'controller' => 'Laravel\\Horizon\\Http\\Controllers\\BatchesController@index',
        'namespace' => 'Laravel\\Horizon\\Http\\Controllers',
        'prefix' => 'horizon/api',
        'where' => 
        array (
        ),
        'as' => 'horizon.jobs-batches.index',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'horizon.jobs-batches.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'horizon/api/batches/{id}',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 'horizon',
        'uses' => 'Laravel\\Horizon\\Http\\Controllers\\BatchesController@show',
        'controller' => 'Laravel\\Horizon\\Http\\Controllers\\BatchesController@show',
        'namespace' => 'Laravel\\Horizon\\Http\\Controllers',
        'prefix' => 'horizon/api',
        'where' => 
        array (
        ),
        'as' => 'horizon.jobs-batches.show',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'horizon.jobs-batches.retry' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'horizon/api/batches/retry/{id}',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 'horizon',
        'uses' => 'Laravel\\Horizon\\Http\\Controllers\\BatchesController@retry',
        'controller' => 'Laravel\\Horizon\\Http\\Controllers\\BatchesController@retry',
        'namespace' => 'Laravel\\Horizon\\Http\\Controllers',
        'prefix' => 'horizon/api',
        'where' => 
        array (
        ),
        'as' => 'horizon.jobs-batches.retry',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'horizon.pending-jobs.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'horizon/api/jobs/pending',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 'horizon',
        'uses' => 'Laravel\\Horizon\\Http\\Controllers\\PendingJobsController@index',
        'controller' => 'Laravel\\Horizon\\Http\\Controllers\\PendingJobsController@index',
        'namespace' => 'Laravel\\Horizon\\Http\\Controllers',
        'prefix' => 'horizon/api',
        'where' => 
        array (
        ),
        'as' => 'horizon.pending-jobs.index',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'horizon.completed-jobs.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'horizon/api/jobs/completed',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 'horizon',
        'uses' => 'Laravel\\Horizon\\Http\\Controllers\\CompletedJobsController@index',
        'controller' => 'Laravel\\Horizon\\Http\\Controllers\\CompletedJobsController@index',
        'namespace' => 'Laravel\\Horizon\\Http\\Controllers',
        'prefix' => 'horizon/api',
        'where' => 
        array (
        ),
        'as' => 'horizon.completed-jobs.index',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'horizon.silenced-jobs.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'horizon/api/jobs/silenced',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 'horizon',
        'uses' => 'Laravel\\Horizon\\Http\\Controllers\\SilencedJobsController@index',
        'controller' => 'Laravel\\Horizon\\Http\\Controllers\\SilencedJobsController@index',
        'namespace' => 'Laravel\\Horizon\\Http\\Controllers',
        'prefix' => 'horizon/api',
        'where' => 
        array (
        ),
        'as' => 'horizon.silenced-jobs.index',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'horizon.failed-jobs.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'horizon/api/jobs/failed',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 'horizon',
        'uses' => 'Laravel\\Horizon\\Http\\Controllers\\FailedJobsController@index',
        'controller' => 'Laravel\\Horizon\\Http\\Controllers\\FailedJobsController@index',
        'namespace' => 'Laravel\\Horizon\\Http\\Controllers',
        'prefix' => 'horizon/api',
        'where' => 
        array (
        ),
        'as' => 'horizon.failed-jobs.index',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'horizon.failed-jobs.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'horizon/api/jobs/failed/{id}',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 'horizon',
        'uses' => 'Laravel\\Horizon\\Http\\Controllers\\FailedJobsController@show',
        'controller' => 'Laravel\\Horizon\\Http\\Controllers\\FailedJobsController@show',
        'namespace' => 'Laravel\\Horizon\\Http\\Controllers',
        'prefix' => 'horizon/api',
        'where' => 
        array (
        ),
        'as' => 'horizon.failed-jobs.show',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'horizon.retry-jobs.show' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'horizon/api/jobs/retry/{id}',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 'horizon',
        'uses' => 'Laravel\\Horizon\\Http\\Controllers\\RetryController@store',
        'controller' => 'Laravel\\Horizon\\Http\\Controllers\\RetryController@store',
        'namespace' => 'Laravel\\Horizon\\Http\\Controllers',
        'prefix' => 'horizon/api',
        'where' => 
        array (
        ),
        'as' => 'horizon.retry-jobs.show',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'horizon.jobs.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'horizon/api/jobs/{id}',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 'horizon',
        'uses' => 'Laravel\\Horizon\\Http\\Controllers\\JobsController@show',
        'controller' => 'Laravel\\Horizon\\Http\\Controllers\\JobsController@show',
        'namespace' => 'Laravel\\Horizon\\Http\\Controllers',
        'prefix' => 'horizon/api',
        'where' => 
        array (
        ),
        'as' => 'horizon.jobs.show',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'horizon.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'horizon/{view?}',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 'horizon',
        'uses' => 'Laravel\\Horizon\\Http\\Controllers\\HomeController@index',
        'controller' => 'Laravel\\Horizon\\Http\\Controllers\\HomeController@index',
        'namespace' => 'Laravel\\Horizon\\Http\\Controllers',
        'prefix' => 'horizon',
        'where' => 
        array (
        ),
        'as' => 'horizon.index',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
        'view' => '(.*)',
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'sanctum.csrf-cookie' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'sanctum/csrf-cookie',
      'action' => 
      array (
        'uses' => 'Laravel\\Sanctum\\Http\\Controllers\\CsrfCookieController@show',
        'controller' => 'Laravel\\Sanctum\\Http\\Controllers\\CsrfCookieController@show',
        'namespace' => NULL,
        'prefix' => 'sanctum',
        'where' => 
        array (
        ),
        'middleware' => 
        array (
          0 => 'web',
        ),
        'as' => 'sanctum.csrf-cookie',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::9MDsbjPI9to9733X' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'POST',
        2 => 'HEAD',
      ),
      'uri' => 'broadcasting/auth',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => '\\Illuminate\\Broadcasting\\BroadcastController@authenticate',
        'controller' => '\\Illuminate\\Broadcasting\\BroadcastController@authenticate',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'excluded_middleware' => 
        array (
          0 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
        ),
        'as' => 'generated::9MDsbjPI9to9733X',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::idBzzapa0f6XGA0J' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'login',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
        ),
        'uses' => 'App\\Http\\Controllers\\AuthController@login',
        'controller' => 'App\\Http\\Controllers\\AuthController@login',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'generated::idBzzapa0f6XGA0J',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::CiMDqsl5aYlgDao3' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'landing-enquiry',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
        ),
        'uses' => 'App\\Http\\Controllers\\LandingEnquiryController@store',
        'controller' => 'App\\Http\\Controllers\\LandingEnquiryController@store',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'generated::CiMDqsl5aYlgDao3',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::E4C5scVTtlikZtJl' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'public/invoices/{uuid}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
        ),
        'uses' => 'App\\Http\\Controllers\\InvoiceController@showPublic',
        'controller' => 'App\\Http\\Controllers\\InvoiceController@showPublic',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'generated::E4C5scVTtlikZtJl',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::Th3ptCAx9v5fmGPj' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'public/invoices/{uuid}/pdf',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
        ),
        'uses' => 'App\\Http\\Controllers\\InvoiceController@generatePublicPdf',
        'controller' => 'App\\Http\\Controllers\\InvoiceController@generatePublicPdf',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'generated::Th3ptCAx9v5fmGPj',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::5hPXfISA53JXYAN4' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'me',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
        ),
        'uses' => 'App\\Http\\Controllers\\AuthController@me',
        'controller' => 'App\\Http\\Controllers\\AuthController@me',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'generated::5hPXfISA53JXYAN4',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::4eJmwtv3tIwit04x' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'logout',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
        ),
        'uses' => 'App\\Http\\Controllers\\AuthController@logout',
        'controller' => 'App\\Http\\Controllers\\AuthController@logout',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'generated::4eJmwtv3tIwit04x',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::JBiP7sCFw0Sim0mP' => 
    array (
      'methods' => 
      array (
        0 => 'PATCH',
      ),
      'uri' => 'settings/profile',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
        ),
        'uses' => 'App\\Http\\Controllers\\SettingsController@updateProfile',
        'controller' => 'App\\Http\\Controllers\\SettingsController@updateProfile',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'generated::JBiP7sCFw0Sim0mP',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::Xi8IASvdukqtDwrT' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/clinical-catalog',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
        ),
        'uses' => 'App\\Http\\Controllers\\ClinicalCatalogController@index',
        'controller' => 'App\\Http\\Controllers\\ClinicalCatalogController@index',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'generated::Xi8IASvdukqtDwrT',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::dQsRL0iQrNUUle25' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/hybrid-suggestions/services',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\AdminHybridSuggestionController@services',
        'controller' => 'App\\Http\\Controllers\\Admin\\AdminHybridSuggestionController@services',
        'namespace' => NULL,
        'prefix' => '/admin/hybrid-suggestions',
        'where' => 
        array (
        ),
        'as' => 'generated::dQsRL0iQrNUUle25',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::Gt5RyaRBDD0KwqIl' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/hybrid-suggestions/medicines',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\AdminHybridSuggestionController@medicines',
        'controller' => 'App\\Http\\Controllers\\Admin\\AdminHybridSuggestionController@medicines',
        'namespace' => NULL,
        'prefix' => '/admin/hybrid-suggestions',
        'where' => 
        array (
        ),
        'as' => 'generated::Gt5RyaRBDD0KwqIl',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::Vw9SoQltVoJHLM3j' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/hybrid-suggestions/master-medicines',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\AdminHybridSuggestionController@masterMedicines',
        'controller' => 'App\\Http\\Controllers\\Admin\\AdminHybridSuggestionController@masterMedicines',
        'namespace' => NULL,
        'prefix' => '/admin/hybrid-suggestions',
        'where' => 
        array (
        ),
        'as' => 'generated::Vw9SoQltVoJHLM3j',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::eqQyAVCSsDUB6Ksg' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/hybrid-promotions/service/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\AdminHybridPromotionController@promoteService',
        'controller' => 'App\\Http\\Controllers\\Admin\\AdminHybridPromotionController@promoteService',
        'namespace' => NULL,
        'prefix' => '/admin/hybrid-promotions',
        'where' => 
        array (
        ),
        'as' => 'generated::eqQyAVCSsDUB6Ksg',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::7tNcfv8KjDBFVocH' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/hybrid-promotions/medicine/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\AdminHybridPromotionController@promoteMedicine',
        'controller' => 'App\\Http\\Controllers\\Admin\\AdminHybridPromotionController@promoteMedicine',
        'namespace' => NULL,
        'prefix' => '/admin/hybrid-promotions',
        'where' => 
        array (
        ),
        'as' => 'generated::7tNcfv8KjDBFVocH',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::ejpXSuNHADNc1REl' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/hybrid-promotions/bulk',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\AdminHybridPromotionController@bulkPromote',
        'controller' => 'App\\Http\\Controllers\\Admin\\AdminHybridPromotionController@bulkPromote',
        'namespace' => NULL,
        'prefix' => '/admin/hybrid-promotions',
        'where' => 
        array (
        ),
        'as' => 'generated::ejpXSuNHADNc1REl',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::PUOjLwYLNzgXuglx' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/hybrid-promotions/approve/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\AdminHybridPromotionController@approvePromotion',
        'controller' => 'App\\Http\\Controllers\\Admin\\AdminHybridPromotionController@approvePromotion',
        'namespace' => NULL,
        'prefix' => '/admin/hybrid-promotions',
        'where' => 
        array (
        ),
        'as' => 'generated::PUOjLwYLNzgXuglx',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::VYFLuIJ3GMPm03np' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/hybrid-promotions/pending',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\AdminHybridPromotionController@listPending',
        'controller' => 'App\\Http\\Controllers\\Admin\\AdminHybridPromotionController@listPending',
        'namespace' => NULL,
        'prefix' => '/admin/hybrid-promotions',
        'where' => 
        array (
        ),
        'as' => 'generated::VYFLuIJ3GMPm03np',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::Ispfgzgs9lxRpx1c' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/hybrid-promotions/reject/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\AdminHybridPromotionController@rejectPromotion',
        'controller' => 'App\\Http\\Controllers\\Admin\\AdminHybridPromotionController@rejectPromotion',
        'namespace' => NULL,
        'prefix' => '/admin/hybrid-promotions',
        'where' => 
        array (
        ),
        'as' => 'generated::Ispfgzgs9lxRpx1c',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::rC2srToaeitykJtZ' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/delete/requests',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\DeletionWorkflowController@listRequests',
        'controller' => 'App\\Http\\Controllers\\Admin\\DeletionWorkflowController@listRequests',
        'namespace' => NULL,
        'prefix' => '/admin/delete',
        'where' => 
        array (
        ),
        'as' => 'generated::rC2srToaeitykJtZ',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::Un5fnLDOr5QRnJoz' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/delete/requests/{id}/drift-check',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\DeletionWorkflowController@driftCheck',
        'controller' => 'App\\Http\\Controllers\\Admin\\DeletionWorkflowController@driftCheck',
        'namespace' => NULL,
        'prefix' => '/admin/delete',
        'where' => 
        array (
        ),
        'as' => 'generated::Un5fnLDOr5QRnJoz',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::NWXLR0aYz88Cpqy9' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/delete/requests/{id}/approve',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\DeletionWorkflowController@approve',
        'controller' => 'App\\Http\\Controllers\\Admin\\DeletionWorkflowController@approve',
        'namespace' => NULL,
        'prefix' => '/admin/delete',
        'where' => 
        array (
        ),
        'as' => 'generated::NWXLR0aYz88Cpqy9',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::3xWGJWFUjTPoleqq' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/delete/requests/{id}/reject',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\DeletionWorkflowController@reject',
        'controller' => 'App\\Http\\Controllers\\Admin\\DeletionWorkflowController@reject',
        'namespace' => NULL,
        'prefix' => '/admin/delete',
        'where' => 
        array (
        ),
        'as' => 'generated::3xWGJWFUjTPoleqq',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::tVI9oozc5K8jYkfz' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/delete/requests/{id}/execute',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\DeletionWorkflowController@execute',
        'controller' => 'App\\Http\\Controllers\\Admin\\DeletionWorkflowController@execute',
        'namespace' => NULL,
        'prefix' => '/admin/delete',
        'where' => 
        array (
        ),
        'as' => 'generated::tVI9oozc5K8jYkfz',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::zRnby8Qh1HhjpPqA' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/delete/{entity}/{id}/request-cascade',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\DeletionWorkflowController@requestCascade',
        'controller' => 'App\\Http\\Controllers\\Admin\\DeletionWorkflowController@requestCascade',
        'namespace' => NULL,
        'prefix' => '/admin/delete',
        'where' => 
        array (
        ),
        'as' => 'generated::zRnby8Qh1HhjpPqA',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::dVbNWAVXX9coVi11' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/delete/{entity}/{id}/summary',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\DeleteManagerController@summary',
        'controller' => 'App\\Http\\Controllers\\Admin\\DeleteManagerController@summary',
        'namespace' => NULL,
        'prefix' => '/admin/delete',
        'where' => 
        array (
        ),
        'as' => 'generated::dVbNWAVXX9coVi11',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::aNwGKixYX9nBnmcJ' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/delete/{entity}/{id}/archive',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\DeleteManagerController@archive',
        'controller' => 'App\\Http\\Controllers\\Admin\\DeleteManagerController@archive',
        'namespace' => NULL,
        'prefix' => '/admin/delete',
        'where' => 
        array (
        ),
        'as' => 'generated::aNwGKixYX9nBnmcJ',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::lERCwsdlqnNoNUUZ' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/delete/{entity}/{id}/restore',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\DeleteManagerController@restore',
        'controller' => 'App\\Http\\Controllers\\Admin\\DeleteManagerController@restore',
        'namespace' => NULL,
        'prefix' => '/admin/delete',
        'where' => 
        array (
        ),
        'as' => 'generated::lERCwsdlqnNoNUUZ',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::bIT9uT8MtBHAJs8s' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/delete/{entity}/{id}/force',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\DeleteManagerController@forceDelete',
        'controller' => 'App\\Http\\Controllers\\Admin\\DeleteManagerController@forceDelete',
        'namespace' => NULL,
        'prefix' => '/admin/delete',
        'where' => 
        array (
        ),
        'as' => 'generated::bIT9uT8MtBHAJs8s',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::CSMDp2oBI789U285' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/delete/{entity}/{id}/force-cascade',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\DeleteManagerController@forceDeleteCascade',
        'controller' => 'App\\Http\\Controllers\\Admin\\DeleteManagerController@forceDeleteCascade',
        'namespace' => NULL,
        'prefix' => '/admin/delete',
        'where' => 
        array (
        ),
        'as' => 'generated::CSMDp2oBI789U285',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::BWokYKzn7reGjKjw' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/delete/{entity}/{id}/cascade-preview',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\DeleteManagerController@cascadePreview',
        'controller' => 'App\\Http\\Controllers\\Admin\\DeleteManagerController@cascadePreview',
        'namespace' => NULL,
        'prefix' => '/admin/delete',
        'where' => 
        array (
        ),
        'as' => 'generated::BWokYKzn7reGjKjw',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::v9ANn8Doq45EX0vs' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/delete/{entity}/bulk',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\DeleteManagerController@bulk',
        'controller' => 'App\\Http\\Controllers\\Admin\\DeleteManagerController@bulk',
        'namespace' => NULL,
        'prefix' => '/admin/delete',
        'where' => 
        array (
        ),
        'as' => 'generated::v9ANn8Doq45EX0vs',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::dwCYGai2PBZDPQdE' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/governance/health',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'auth:sanctum',
          5 => 'role:super_admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\GovernanceController@health',
        'controller' => 'App\\Http\\Controllers\\Admin\\GovernanceController@health',
        'namespace' => NULL,
        'prefix' => '/admin/governance',
        'where' => 
        array (
        ),
        'as' => 'generated::dwCYGai2PBZDPQdE',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::nhbTl8bRgiuXVqBK' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/governance/repair/duplicate-services',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'auth:sanctum',
          5 => 'role:super_admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\GovernanceController@repairDuplicateServices',
        'controller' => 'App\\Http\\Controllers\\Admin\\GovernanceController@repairDuplicateServices',
        'namespace' => NULL,
        'prefix' => '/admin/governance',
        'where' => 
        array (
        ),
        'as' => 'generated::nhbTl8bRgiuXVqBK',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::OYsU2ygL4SrOOxhg' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/governance/repair/duplicate-medicines',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'auth:sanctum',
          5 => 'role:super_admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\GovernanceController@repairDuplicateMedicines',
        'controller' => 'App\\Http\\Controllers\\Admin\\GovernanceController@repairDuplicateMedicines',
        'namespace' => NULL,
        'prefix' => '/admin/governance',
        'where' => 
        array (
        ),
        'as' => 'generated::OYsU2ygL4SrOOxhg',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::enBNfUHo27SPssEP' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/governance/repair/inventory-batches',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'auth:sanctum',
          5 => 'role:super_admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\GovernanceController@repairInventoryBatches',
        'controller' => 'App\\Http\\Controllers\\Admin\\GovernanceController@repairInventoryBatches',
        'namespace' => NULL,
        'prefix' => '/admin/governance',
        'where' => 
        array (
        ),
        'as' => 'generated::enBNfUHo27SPssEP',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::srPbamQwNdB5bkyq' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/governance/repair/orphan-treatments',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'auth:sanctum',
          5 => 'role:super_admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\GovernanceController@repairOrphanTreatments',
        'controller' => 'App\\Http\\Controllers\\Admin\\GovernanceController@repairOrphanTreatments',
        'namespace' => NULL,
        'prefix' => '/admin/governance',
        'where' => 
        array (
        ),
        'as' => 'generated::srPbamQwNdB5bkyq',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::OCTRWEPBLbQIcTAZ' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/governance/repair/negative-inventory',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'auth:sanctum',
          5 => 'role:super_admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\GovernanceController@repairNegativeInventory',
        'controller' => 'App\\Http\\Controllers\\Admin\\GovernanceController@repairNegativeInventory',
        'namespace' => NULL,
        'prefix' => '/admin/governance',
        'where' => 
        array (
        ),
        'as' => 'generated::OCTRWEPBLbQIcTAZ',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::dt7CZIUJgRDwlHsI' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/governance/repair/medicine-drift',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'auth:sanctum',
          5 => 'role:super_admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\GovernanceController@repairMedicineDrift',
        'controller' => 'App\\Http\\Controllers\\Admin\\GovernanceController@repairMedicineDrift',
        'namespace' => NULL,
        'prefix' => '/admin/governance',
        'where' => 
        array (
        ),
        'as' => 'generated::dt7CZIUJgRDwlHsI',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::ChnBHfn53HmlQlf5' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/governance/repair/floating-ledger',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'auth:sanctum',
          5 => 'role:super_admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\GovernanceController@repairFloatingLedger',
        'controller' => 'App\\Http\\Controllers\\Admin\\GovernanceController@repairFloatingLedger',
        'namespace' => NULL,
        'prefix' => '/admin/governance',
        'where' => 
        array (
        ),
        'as' => 'generated::ChnBHfn53HmlQlf5',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'patients.full' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'patients/{patient}/full',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:doctor,staff',
          5 => 'subscription',
          6 => 'check.permission:patients',
          7 => 'module:auto',
        ),
        'uses' => 'App\\Http\\Controllers\\PatientController@full',
        'controller' => 'App\\Http\\Controllers\\PatientController@full',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'patients.full',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'patients.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'patients',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:doctor,staff',
          5 => 'subscription',
          6 => 'check.permission:patients',
          7 => 'module:auto',
        ),
        'as' => 'patients.index',
        'uses' => 'App\\Http\\Controllers\\PatientController@index',
        'controller' => 'App\\Http\\Controllers\\PatientController@index',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'patients.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'patients',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:doctor,staff',
          5 => 'subscription',
          6 => 'check.permission:patients',
          7 => 'module:auto',
        ),
        'as' => 'patients.store',
        'uses' => 'App\\Http\\Controllers\\PatientController@store',
        'controller' => 'App\\Http\\Controllers\\PatientController@store',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'patients.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'patients/{patient}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:doctor,staff',
          5 => 'subscription',
          6 => 'check.permission:patients',
          7 => 'module:auto',
        ),
        'as' => 'patients.show',
        'uses' => 'App\\Http\\Controllers\\PatientController@show',
        'controller' => 'App\\Http\\Controllers\\PatientController@show',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'patients.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'patients/{patient}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:doctor,staff',
          5 => 'subscription',
          6 => 'check.permission:patients',
          7 => 'module:auto',
        ),
        'as' => 'patients.update',
        'uses' => 'App\\Http\\Controllers\\PatientController@update',
        'controller' => 'App\\Http\\Controllers\\PatientController@update',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'appointments.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'appointments',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:doctor,staff',
          5 => 'subscription',
          6 => 'check.permission:appointments',
          7 => 'module:auto',
        ),
        'as' => 'appointments.index',
        'uses' => 'App\\Http\\Controllers\\AppointmentController@index',
        'controller' => 'App\\Http\\Controllers\\AppointmentController@index',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'appointments.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'appointments',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:doctor,staff',
          5 => 'subscription',
          6 => 'check.permission:appointments',
          7 => 'module:auto',
        ),
        'as' => 'appointments.store',
        'uses' => 'App\\Http\\Controllers\\AppointmentController@store',
        'controller' => 'App\\Http\\Controllers\\AppointmentController@store',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'appointments.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'appointments/{appointment}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:doctor,staff',
          5 => 'subscription',
          6 => 'check.permission:appointments',
          7 => 'module:auto',
        ),
        'as' => 'appointments.show',
        'uses' => 'App\\Http\\Controllers\\AppointmentController@show',
        'controller' => 'App\\Http\\Controllers\\AppointmentController@show',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'appointments.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'appointments/{appointment}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:doctor,staff',
          5 => 'subscription',
          6 => 'check.permission:appointments',
          7 => 'module:auto',
        ),
        'as' => 'appointments.update',
        'uses' => 'App\\Http\\Controllers\\AppointmentController@update',
        'controller' => 'App\\Http\\Controllers\\AppointmentController@update',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'appointments.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'appointments/{appointment}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:doctor,staff',
          5 => 'subscription',
          6 => 'check.permission:appointments',
          7 => 'module:auto',
        ),
        'as' => 'appointments.destroy',
        'uses' => 'App\\Http\\Controllers\\AppointmentController@destroy',
        'controller' => 'App\\Http\\Controllers\\AppointmentController@destroy',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'billing.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'invoices',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:doctor,staff',
          5 => 'subscription',
          6 => 'role:doctor',
          7 => 'module:auto',
        ),
        'uses' => 'App\\Http\\Controllers\\InvoiceController@index',
        'controller' => 'App\\Http\\Controllers\\InvoiceController@index',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'billing.index',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'billing.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'invoices/{invoice}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:doctor,staff',
          5 => 'subscription',
          6 => 'role:doctor',
          7 => 'module:auto',
        ),
        'uses' => 'App\\Http\\Controllers\\InvoiceController@show',
        'controller' => 'App\\Http\\Controllers\\InvoiceController@show',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'billing.show',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'billing.pdf' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'invoices/{id}/pdf',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:doctor,staff',
          5 => 'subscription',
          6 => 'role:doctor',
          7 => 'module:auto',
        ),
        'uses' => 'App\\Http\\Controllers\\InvoiceController@generatePdf',
        'controller' => 'App\\Http\\Controllers\\InvoiceController@generatePdf',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'billing.pdf',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'billing.invoices.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'invoices',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:doctor,staff',
          5 => 'subscription',
          6 => 'check.permission:billing_write',
          7 => 'module:auto',
          8 => 'block.receptionist',
        ),
        'uses' => 'App\\Http\\Controllers\\InvoiceController@store',
        'controller' => 'App\\Http\\Controllers\\InvoiceController@store',
        'as' => 'billing.invoices.store',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'billing.invoices.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'uri' => 'invoices/{invoice}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:doctor,staff',
          5 => 'subscription',
          6 => 'check.permission:billing_write',
          7 => 'module:auto',
        ),
        'uses' => 'App\\Http\\Controllers\\InvoiceController@update',
        'controller' => 'App\\Http\\Controllers\\InvoiceController@update',
        'as' => 'billing.invoices.update',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'billing.invoices.finalize' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'invoices/{invoice}/finalize',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:doctor,staff',
          5 => 'subscription',
          6 => 'check.permission:billing_write',
          7 => 'module:auto',
          8 => 'block.receptionist',
        ),
        'uses' => 'App\\Http\\Controllers\\InvoiceController@finalize',
        'controller' => 'App\\Http\\Controllers\\InvoiceController@finalize',
        'as' => 'billing.invoices.finalize',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'billing.invoices.status' => 
    array (
      'methods' => 
      array (
        0 => 'PATCH',
      ),
      'uri' => 'invoices/{invoice}/status',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:doctor,staff',
          5 => 'subscription',
          6 => 'check.permission:billing_write',
          7 => 'module:auto',
          8 => 'block.receptionist',
        ),
        'uses' => 'App\\Http\\Controllers\\InvoiceController@updateStatus',
        'controller' => 'App\\Http\\Controllers\\InvoiceController@updateStatus',
        'as' => 'billing.invoices.status',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'billing.invoices.apply_payment' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'invoices/{invoice}/apply-payment',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:doctor,staff',
          5 => 'subscription',
          6 => 'check.permission:billing_write',
          7 => 'module:auto',
          8 => 'block.receptionist',
        ),
        'uses' => 'App\\Http\\Controllers\\InvoiceController@applyPayment',
        'controller' => 'App\\Http\\Controllers\\InvoiceController@applyPayment',
        'as' => 'billing.invoices.apply_payment',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'billing.' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'invoices/{invoice}/approve-reallocation',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:doctor,staff',
          5 => 'subscription',
          6 => 'check.permission:billing_write',
          7 => 'module:auto',
        ),
        'uses' => 'App\\Http\\Controllers\\InvoiceController@approveReallocation',
        'controller' => 'App\\Http\\Controllers\\InvoiceController@approveReallocation',
        'as' => 'billing.',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'expenses.summary' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'expenses/summary',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:doctor,staff',
          5 => 'subscription',
          6 => 'check.permission:billing_write',
          7 => 'module:auto',
        ),
        'uses' => 'App\\Http\\Controllers\\ExpenseController@summary',
        'controller' => 'App\\Http\\Controllers\\ExpenseController@summary',
        'as' => 'expenses.summary',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'expenses.expenses.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'expenses',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:doctor,staff',
          5 => 'subscription',
          6 => 'check.permission:billing_write',
          7 => 'module:auto',
        ),
        'as' => 'expenses.expenses.index',
        'uses' => 'App\\Http\\Controllers\\ExpenseController@index',
        'controller' => 'App\\Http\\Controllers\\ExpenseController@index',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'expenses.expenses.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'expenses',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:doctor,staff',
          5 => 'subscription',
          6 => 'check.permission:billing_write',
          7 => 'module:auto',
        ),
        'as' => 'expenses.expenses.store',
        'uses' => 'App\\Http\\Controllers\\ExpenseController@store',
        'controller' => 'App\\Http\\Controllers\\ExpenseController@store',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'expenses.expenses.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'expenses/{expense}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:doctor,staff',
          5 => 'subscription',
          6 => 'check.permission:billing_write',
          7 => 'module:auto',
        ),
        'as' => 'expenses.expenses.show',
        'uses' => 'App\\Http\\Controllers\\ExpenseController@show',
        'controller' => 'App\\Http\\Controllers\\ExpenseController@show',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'expenses.expenses.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'expenses/{expense}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:doctor,staff',
          5 => 'subscription',
          6 => 'check.permission:billing_write',
          7 => 'module:auto',
        ),
        'as' => 'expenses.expenses.update',
        'uses' => 'App\\Http\\Controllers\\ExpenseController@update',
        'controller' => 'App\\Http\\Controllers\\ExpenseController@update',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'clinical-catalog.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'clinical-catalog',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:doctor,staff',
          5 => 'subscription',
          6 => 'check.permission:clinical',
          7 => 'module:auto',
        ),
        'uses' => 'App\\Http\\Controllers\\ClinicalCatalogController@index',
        'controller' => 'App\\Http\\Controllers\\ClinicalCatalogController@index',
        'as' => 'clinical-catalog.index',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'clinical-catalog.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'clinical-catalog',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:doctor,staff',
          5 => 'subscription',
          6 => 'check.permission:clinical',
          7 => 'module:auto',
          8 => 'require.doctor.role',
        ),
        'uses' => 'App\\Http\\Controllers\\ClinicalCatalogController@store',
        'controller' => 'App\\Http\\Controllers\\ClinicalCatalogController@store',
        'as' => 'clinical-catalog.store',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'clinical-catalog.settings' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'clinical-catalog/settings',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:doctor,staff',
          5 => 'subscription',
          6 => 'check.permission:clinical',
          7 => 'module:auto',
          8 => 'require.doctor.role',
        ),
        'uses' => 'App\\Http\\Controllers\\ClinicalCatalogController@updateSettings',
        'controller' => 'App\\Http\\Controllers\\ClinicalCatalogController@updateSettings',
        'as' => 'clinical-catalog.settings',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'clinical-catalog.treatments.by_patient' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'patients/{patient}/treatments',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:doctor,staff',
          5 => 'subscription',
          6 => 'check.permission:clinical',
          7 => 'module:auto',
          8 => 'block.receptionist',
        ),
        'uses' => 'App\\Http\\Controllers\\TreatmentController@getByPatient',
        'controller' => 'App\\Http\\Controllers\\TreatmentController@getByPatient',
        'as' => 'clinical-catalog.treatments.by_patient',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'clinical-catalog.treatments.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'treatments',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:doctor,staff',
          5 => 'subscription',
          6 => 'check.permission:clinical',
          7 => 'module:auto',
          8 => 'block.receptionist',
        ),
        'uses' => 'App\\Http\\Controllers\\TreatmentController@store',
        'controller' => 'App\\Http\\Controllers\\TreatmentController@store',
        'as' => 'clinical-catalog.treatments.store',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'clinical-catalog.treatments.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'uri' => 'treatments/{treatment}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:doctor,staff',
          5 => 'subscription',
          6 => 'check.permission:clinical',
          7 => 'module:auto',
          8 => 'block.receptionist',
        ),
        'uses' => 'App\\Http\\Controllers\\TreatmentController@update',
        'controller' => 'App\\Http\\Controllers\\TreatmentController@update',
        'as' => 'clinical-catalog.treatments.update',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'clinical-catalog.treatments.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'treatments',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:doctor,staff',
          5 => 'subscription',
          6 => 'check.permission:clinical',
          7 => 'module:auto',
        ),
        'as' => 'clinical-catalog.treatments.index',
        'uses' => 'App\\Http\\Controllers\\TreatmentController@index',
        'controller' => 'App\\Http\\Controllers\\TreatmentController@index',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'clinical-catalog.treatments.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'treatments/{treatment}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:doctor,staff',
          5 => 'subscription',
          6 => 'check.permission:clinical',
          7 => 'module:auto',
        ),
        'as' => 'clinical-catalog.treatments.show',
        'uses' => 'App\\Http\\Controllers\\TreatmentController@show',
        'controller' => 'App\\Http\\Controllers\\TreatmentController@show',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'inventory.catalog' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'pharmacy/catalog',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:doctor,staff',
          5 => 'subscription',
          6 => 'check.permission:pharmacy',
          7 => 'module:auto',
        ),
        'uses' => 'App\\Http\\Controllers\\Doctor\\PharmacyController@catalog',
        'controller' => 'App\\Http\\Controllers\\Doctor\\PharmacyController@catalog',
        'as' => 'inventory.catalog',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'inventory.' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'inventory/search-medicines',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:doctor,staff',
          5 => 'subscription',
          6 => 'check.permission:pharmacy',
          7 => 'module:auto',
        ),
        'uses' => 'App\\Http\\Controllers\\InventoryController@searchMedicines',
        'controller' => 'App\\Http\\Controllers\\InventoryController@searchMedicines',
        'as' => 'inventory.',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'inventory.analytics' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'inventory/analytics',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:doctor,staff',
          5 => 'subscription',
          6 => 'check.permission:pharmacy',
          7 => 'module:auto',
        ),
        'uses' => 'App\\Http\\Controllers\\InventoryController@analytics',
        'controller' => 'App\\Http\\Controllers\\InventoryController@analytics',
        'as' => 'inventory.analytics',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'inventory.replenish' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'inventory/{inventory}/replenish',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:doctor,staff',
          5 => 'subscription',
          6 => 'check.permission:pharmacy',
          7 => 'module:auto',
          8 => 'block.receptionist',
        ),
        'uses' => 'App\\Http\\Controllers\\InventoryController@replenish',
        'controller' => 'App\\Http\\Controllers\\InventoryController@replenish',
        'as' => 'inventory.replenish',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'inventory.adjust' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'inventory/{inventory}/adjust',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:doctor,staff',
          5 => 'subscription',
          6 => 'check.permission:pharmacy',
          7 => 'module:auto',
          8 => 'block.receptionist',
        ),
        'uses' => 'App\\Http\\Controllers\\InventoryController@adjust',
        'controller' => 'App\\Http\\Controllers\\InventoryController@adjust',
        'as' => 'inventory.adjust',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'inventory.generated::o5C5KYQqeHrWtvC9' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'inventory',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:doctor,staff',
          5 => 'subscription',
          6 => 'check.permission:pharmacy',
          7 => 'module:auto',
          8 => 'block.receptionist',
        ),
        'uses' => 'App\\Http\\Controllers\\InventoryController@store',
        'controller' => 'App\\Http\\Controllers\\InventoryController@store',
        'as' => 'inventory.generated::o5C5KYQqeHrWtvC9',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'inventory.generated::Ruf6EWRQkUk2jC6F' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'inventory/{inventory}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:doctor,staff',
          5 => 'subscription',
          6 => 'check.permission:pharmacy',
          7 => 'module:auto',
          8 => 'block.receptionist',
        ),
        'uses' => 'App\\Http\\Controllers\\InventoryController@update',
        'controller' => 'App\\Http\\Controllers\\InventoryController@update',
        'as' => 'inventory.generated::Ruf6EWRQkUk2jC6F',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'inventory.inventory.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'inventory',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:doctor,staff',
          5 => 'subscription',
          6 => 'check.permission:pharmacy',
          7 => 'module:auto',
        ),
        'as' => 'inventory.inventory.index',
        'uses' => 'App\\Http\\Controllers\\InventoryController@index',
        'controller' => 'App\\Http\\Controllers\\InventoryController@index',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'inventory.inventory.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'inventory/{inventory}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:doctor,staff',
          5 => 'subscription',
          6 => 'check.permission:pharmacy',
          7 => 'module:auto',
        ),
        'as' => 'inventory.inventory.show',
        'uses' => 'App\\Http\\Controllers\\InventoryController@show',
        'controller' => 'App\\Http\\Controllers\\InventoryController@show',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::fFOOM4YF0h7wfC0O' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'staff/{id}/activity',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:doctor,staff',
          5 => 'subscription',
          6 => 'check.permission:settings',
        ),
        'uses' => 'App\\Http\\Controllers\\StaffController@activity',
        'controller' => 'App\\Http\\Controllers\\StaffController@activity',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'generated::fFOOM4YF0h7wfC0O',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'staff.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'staff',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:doctor,staff',
          5 => 'subscription',
          6 => 'check.permission:settings',
          7 => 'require.doctor.role',
        ),
        'uses' => 'App\\Http\\Controllers\\StaffController@store',
        'controller' => 'App\\Http\\Controllers\\StaffController@store',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'staff.store',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::4fINwN72yvetFDm0' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'staff/me',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:doctor,staff',
          5 => 'subscription',
          6 => 'check.permission:settings',
          7 => 'role:doctor,staff',
        ),
        'uses' => 'App\\Http\\Controllers\\StaffController@me',
        'controller' => 'App\\Http\\Controllers\\StaffController@me',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'excluded_middleware' => 
        array (
          0 => 'check.permission:settings',
        ),
        'as' => 'generated::4fINwN72yvetFDm0',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::Uv3IG0yzfKE7ej2Y' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'staff/{staff}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:doctor,staff',
          5 => 'subscription',
          6 => 'check.permission:settings',
          7 => 'require.doctor.role',
        ),
        'uses' => 'App\\Http\\Controllers\\StaffController@update',
        'controller' => 'App\\Http\\Controllers\\StaffController@update',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'generated::Uv3IG0yzfKE7ej2Y',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'staff.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'staff',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:doctor,staff',
          5 => 'subscription',
          6 => 'check.permission:settings',
        ),
        'as' => 'staff.index',
        'uses' => 'App\\Http\\Controllers\\StaffController@index',
        'controller' => 'App\\Http\\Controllers\\StaffController@index',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'staff.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'staff/{staff}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:doctor,staff',
          5 => 'subscription',
          6 => 'check.permission:settings',
        ),
        'as' => 'staff.show',
        'uses' => 'App\\Http\\Controllers\\StaffController@show',
        'controller' => 'App\\Http\\Controllers\\StaffController@show',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::NVxzPwFc97JclDty' => 
    array (
      'methods' => 
      array (
        0 => 'PATCH',
      ),
      'uri' => 'settings/branding',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:doctor,staff',
          5 => 'subscription',
          6 => 'check.permission:settings',
        ),
        'uses' => 'App\\Http\\Controllers\\SettingsController@updateBranding',
        'controller' => 'App\\Http\\Controllers\\SettingsController@updateBranding',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'generated::NVxzPwFc97JclDty',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::UCCRFnBASFUUuORh' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'settings/logo',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:doctor,staff',
          5 => 'subscription',
          6 => 'check.permission:settings',
        ),
        'uses' => 'App\\Http\\Controllers\\SettingsController@updateLogo',
        'controller' => 'App\\Http\\Controllers\\SettingsController@updateLogo',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'generated::UCCRFnBASFUUuORh',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::akZKkzRTRfGKurPS' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'settings',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:doctor,staff',
          5 => 'subscription',
        ),
        'uses' => 'App\\Http\\Controllers\\SettingsController@getSettings',
        'controller' => 'App\\Http\\Controllers\\SettingsController@getSettings',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'generated::akZKkzRTRfGKurPS',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'insights.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'growth/insights',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:doctor,staff',
          5 => 'subscription',
          6 => 'check.permission:reports',
          7 => 'module:auto',
        ),
        'uses' => 'App\\Http\\Controllers\\GrowthInsightController@index',
        'controller' => 'App\\Http\\Controllers\\GrowthInsightController@index',
        'as' => 'insights.index',
        'namespace' => NULL,
        'prefix' => '/growth',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'expenses.finance_summary' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'finance/summary',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:doctor,staff',
          5 => 'subscription',
          6 => 'role:doctor',
          7 => 'module:auto',
        ),
        'uses' => 'App\\Http\\Controllers\\FinanceController@summary',
        'controller' => 'App\\Http\\Controllers\\FinanceController@summary',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'expenses.finance_summary',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'expenses.revenue_trend' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'finance/revenue-trend',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:doctor,staff',
          5 => 'subscription',
          6 => 'role:doctor',
          7 => 'module:auto',
        ),
        'uses' => 'App\\Http\\Controllers\\FinanceController@revenueTrend',
        'controller' => 'App\\Http\\Controllers\\FinanceController@revenueTrend',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'expenses.revenue_trend',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'expenses.integrity' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'system/integrity',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:doctor,staff',
          5 => 'subscription',
          6 => 'role:doctor',
          7 => 'module:auto',
        ),
        'uses' => 'App\\Http\\Controllers\\SystemController@integrity',
        'controller' => 'App\\Http\\Controllers\\SystemController@integrity',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'expenses.integrity',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'audit_logs.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'audit-logs',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:doctor,staff',
          5 => 'subscription',
          6 => 'role:doctor',
          7 => 'module:auto',
        ),
        'uses' => 'App\\Http\\Controllers\\AuditLogController@index',
        'controller' => 'App\\Http\\Controllers\\AuditLogController@index',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'audit_logs.index',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::SZTPFKFhzoQzobmy' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'subscription/usage',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:doctor,staff',
          5 => 'subscription',
          6 => 'role:doctor',
        ),
        'uses' => 'App\\Http\\Controllers\\Doctor\\SubscriptionUsageController@usage',
        'controller' => 'App\\Http\\Controllers\\Doctor\\SubscriptionUsageController@usage',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'generated::SZTPFKFhzoQzobmy',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::sVfcu6zb5ze5CSWd' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'subscription/me',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:doctor,staff',
          5 => 'subscription',
          6 => 'role:doctor',
        ),
        'uses' => 'App\\Http\\Controllers\\Doctor\\DoctorSubscriptionController@me',
        'controller' => 'App\\Http\\Controllers\\Doctor\\DoctorSubscriptionController@me',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'generated::sVfcu6zb5ze5CSWd',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::rnKXPFpZP1xyXjLD' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'notifications',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:doctor,staff',
          5 => 'subscription',
        ),
        'uses' => 'App\\Http\\Controllers\\NotificationController@index',
        'controller' => 'App\\Http\\Controllers\\NotificationController@index',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'generated::rnKXPFpZP1xyXjLD',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::mTuHNNR5NAAG7A0l' => 
    array (
      'methods' => 
      array (
        0 => 'PATCH',
      ),
      'uri' => 'notifications/{notification}/read',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:doctor,staff',
          5 => 'subscription',
        ),
        'uses' => 'App\\Http\\Controllers\\NotificationController@markAsRead',
        'controller' => 'App\\Http\\Controllers\\NotificationController@markAsRead',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'generated::mTuHNNR5NAAG7A0l',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::DbTiBhMqkqlKgeZ1' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'notifications/read-all',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:doctor,staff',
          5 => 'subscription',
        ),
        'uses' => 'App\\Http\\Controllers\\NotificationController@markAllRead',
        'controller' => 'App\\Http\\Controllers\\NotificationController@markAllRead',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'generated::DbTiBhMqkqlKgeZ1',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::uZ1W8qUHXPK7qE1q' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/stop-impersonation',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
        ),
        'uses' => 'App\\Http\\Controllers\\AdminImpersonationController@stop',
        'controller' => 'App\\Http\\Controllers\\AdminImpersonationController@stop',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'generated::uZ1W8qUHXPK7qE1q',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::V3LDFoNziJZ021f1' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/impersonate/{doctor}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:super_admin',
        ),
        'uses' => 'App\\Http\\Controllers\\AdminImpersonationController@impersonate',
        'controller' => 'App\\Http\\Controllers\\AdminImpersonationController@impersonate',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
        'as' => 'generated::V3LDFoNziJZ021f1',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::VNqUN8baJ47qKGut' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/dashboard/stats',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:super_admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\DashboardController@stats',
        'controller' => 'App\\Http\\Controllers\\Admin\\DashboardController@stats',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
        'as' => 'generated::VNqUN8baJ47qKGut',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::CICiSb5QEmn6R6Tn' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/doctors',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:super_admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\DoctorController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\DoctorController@index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
        'as' => 'generated::CICiSb5QEmn6R6Tn',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::SKx3Bg2PpnDEOOm0' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/doctors/reference-data',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:super_admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\DoctorController@getReferenceData',
        'controller' => 'App\\Http\\Controllers\\Admin\\DoctorController@getReferenceData',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
        'as' => 'generated::SKx3Bg2PpnDEOOm0',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::UuXHoOU1rSreXOip' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/doctors',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:super_admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\DoctorController@store',
        'controller' => 'App\\Http\\Controllers\\Admin\\DoctorController@store',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
        'as' => 'generated::UuXHoOU1rSreXOip',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::8mWVs5Pywvybg8Gp' => 
    array (
      'methods' => 
      array (
        0 => 'PATCH',
      ),
      'uri' => 'admin/doctors/{user}/toggle-active',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:super_admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\DoctorController@toggleActive',
        'controller' => 'App\\Http\\Controllers\\Admin\\DoctorController@toggleActive',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
        'as' => 'generated::8mWVs5Pywvybg8Gp',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::yYgByd0ZvZ4AxMhK' => 
    array (
      'methods' => 
      array (
        0 => 'PATCH',
      ),
      'uri' => 'admin/doctors/{user}/reset-password',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:super_admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\DoctorController@resetPassword',
        'controller' => 'App\\Http\\Controllers\\Admin\\DoctorController@resetPassword',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
        'as' => 'generated::yYgByd0ZvZ4AxMhK',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::mQaQW8y2C3UtCBP1' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/doctors/{user}/logs',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:super_admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\DoctorController@getLogs',
        'controller' => 'App\\Http\\Controllers\\Admin\\DoctorController@getLogs',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
        'as' => 'generated::mQaQW8y2C3UtCBP1',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::fZ6Jgti3gJjDfMoJ' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/doctors/{user}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:super_admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\DoctorController@show',
        'controller' => 'App\\Http\\Controllers\\Admin\\DoctorController@show',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
        'as' => 'generated::fZ6Jgti3gJjDfMoJ',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::DScO5CeVdK4njjlH' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/plans',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:super_admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\PlanController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\PlanController@index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
        'as' => 'generated::DScO5CeVdK4njjlH',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::gZv3JkWAB0MRNDTY' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/plans',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:super_admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\PlanController@store',
        'controller' => 'App\\Http\\Controllers\\Admin\\PlanController@store',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
        'as' => 'generated::gZv3JkWAB0MRNDTY',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::ne5pslnQrv9ytYSs' => 
    array (
      'methods' => 
      array (
        0 => 'PATCH',
      ),
      'uri' => 'admin/plans/{plan}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:super_admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\PlanController@update',
        'controller' => 'App\\Http\\Controllers\\Admin\\PlanController@update',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
        'as' => 'generated::ne5pslnQrv9ytYSs',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::h80eZqXyOG0WJo8c' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/specialties/archived',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:super_admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\SpecialtyController@archived',
        'controller' => 'App\\Http\\Controllers\\Admin\\SpecialtyController@archived',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
        'as' => 'generated::h80eZqXyOG0WJo8c',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::QShSuzyV28fLdjWO' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/services/archived',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:super_admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\ClinicalCatalogManagerController@archived',
        'controller' => 'App\\Http\\Controllers\\Admin\\ClinicalCatalogManagerController@archived',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
        'as' => 'generated::QShSuzyV28fLdjWO',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::Ka2FhvCusJjVJ3uY' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/medicines/archived',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:super_admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\PharmacyCatalogManagerController@archived',
        'controller' => 'App\\Http\\Controllers\\Admin\\PharmacyCatalogManagerController@archived',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
        'as' => 'generated::Ka2FhvCusJjVJ3uY',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'specialties.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/specialties',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:super_admin',
        ),
        'as' => 'specialties.index',
        'uses' => 'App\\Http\\Controllers\\Admin\\SpecialtyController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\SpecialtyController@index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'specialties.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/specialties',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:super_admin',
        ),
        'as' => 'specialties.store',
        'uses' => 'App\\Http\\Controllers\\Admin\\SpecialtyController@store',
        'controller' => 'App\\Http\\Controllers\\Admin\\SpecialtyController@store',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'specialties.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/specialties/{specialty}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:super_admin',
        ),
        'as' => 'specialties.show',
        'uses' => 'App\\Http\\Controllers\\Admin\\SpecialtyController@show',
        'controller' => 'App\\Http\\Controllers\\Admin\\SpecialtyController@show',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'specialties.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'admin/specialties/{specialty}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:super_admin',
        ),
        'as' => 'specialties.update',
        'uses' => 'App\\Http\\Controllers\\Admin\\SpecialtyController@update',
        'controller' => 'App\\Http\\Controllers\\Admin\\SpecialtyController@update',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'specialties.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/specialties/{specialty}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:super_admin',
        ),
        'as' => 'specialties.destroy',
        'uses' => 'App\\Http\\Controllers\\Admin\\SpecialtyController@destroy',
        'controller' => 'App\\Http\\Controllers\\Admin\\SpecialtyController@destroy',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::lOZJXsnQkb3AK401' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/specialties/{specialty}/catalog',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:super_admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\ClinicalCatalogManagerController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\ClinicalCatalogManagerController@index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
        'as' => 'generated::lOZJXsnQkb3AK401',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::V9gqHT6FkRFNLgTy' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/specialties/{specialty}/categories',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:super_admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\ClinicalCatalogManagerController@storeCategory',
        'controller' => 'App\\Http\\Controllers\\Admin\\ClinicalCatalogManagerController@storeCategory',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
        'as' => 'generated::V9gqHT6FkRFNLgTy',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::3HK3fKyY2QkNqAmY' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'uri' => 'admin/categories/{category}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:super_admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\ClinicalCatalogManagerController@updateCategory',
        'controller' => 'App\\Http\\Controllers\\Admin\\ClinicalCatalogManagerController@updateCategory',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
        'as' => 'generated::3HK3fKyY2QkNqAmY',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::xiPWJ60OQ3UG853r' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/specialties/{specialty}/services',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:super_admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\ClinicalCatalogManagerController@storeService',
        'controller' => 'App\\Http\\Controllers\\Admin\\ClinicalCatalogManagerController@storeService',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
        'as' => 'generated::xiPWJ60OQ3UG853r',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::TLwXssY8Ef9ksxpK' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'uri' => 'admin/services/{item}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:super_admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\ClinicalCatalogManagerController@updateService',
        'controller' => 'App\\Http\\Controllers\\Admin\\ClinicalCatalogManagerController@updateService',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
        'as' => 'generated::TLwXssY8Ef9ksxpK',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::YSqXv5sx0mFAafro' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/specialties/{specialty}/pharmacy-catalog',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:super_admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\PharmacyCatalogManagerController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\PharmacyCatalogManagerController@index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
        'as' => 'generated::YSqXv5sx0mFAafro',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::P9me0azdR9pPvRfG' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/specialties/{specialty}/pharmacy-categories',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:super_admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\PharmacyCatalogManagerController@storeCategory',
        'controller' => 'App\\Http\\Controllers\\Admin\\PharmacyCatalogManagerController@storeCategory',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
        'as' => 'generated::P9me0azdR9pPvRfG',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::iwWG4A6BXq53H2zH' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'uri' => 'admin/pharmacy-categories/{category}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:super_admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\PharmacyCatalogManagerController@updateCategory',
        'controller' => 'App\\Http\\Controllers\\Admin\\PharmacyCatalogManagerController@updateCategory',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
        'as' => 'generated::iwWG4A6BXq53H2zH',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::MBaSk4UihkaEMCof' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/specialties/{specialty}/medicines',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:super_admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\PharmacyCatalogManagerController@storeMedicine',
        'controller' => 'App\\Http\\Controllers\\Admin\\PharmacyCatalogManagerController@storeMedicine',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
        'as' => 'generated::MBaSk4UihkaEMCof',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::OqmnxJGpOzaZld0b' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'uri' => 'admin/medicines/{medicine}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:super_admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\PharmacyCatalogManagerController@updateMedicine',
        'controller' => 'App\\Http\\Controllers\\Admin\\PharmacyCatalogManagerController@updateMedicine',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
        'as' => 'generated::OqmnxJGpOzaZld0b',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::UufYrM37braLBoY5' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/clinical-catalog/import',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:super_admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\ClinicalCatalogManagerController@import',
        'controller' => 'App\\Http\\Controllers\\Admin\\ClinicalCatalogManagerController@import',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
        'as' => 'generated::UufYrM37braLBoY5',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::JQiANljufk70lfHg' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/pharmacy-catalog/import',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:super_admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\PharmacyCatalogManagerController@import',
        'controller' => 'App\\Http\\Controllers\\Admin\\PharmacyCatalogManagerController@import',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
        'as' => 'generated::JQiANljufk70lfHg',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::S9M3ExCOhhxYP5ia' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/system-settings',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:super_admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\AdminSystemSettingController@store',
        'controller' => 'App\\Http\\Controllers\\Admin\\AdminSystemSettingController@store',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
        'as' => 'generated::S9M3ExCOhhxYP5ia',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::nbA2iNfKBYhMIubh' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/system-settings/{key}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:super_admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\AdminSystemSettingController@get',
        'controller' => 'App\\Http\\Controllers\\Admin\\AdminSystemSettingController@get',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
        'as' => 'generated::nbA2iNfKBYhMIubh',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::yYr5Kp9Bdv5hyd6g' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/service-submissions',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:super_admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\AdminServiceModerationController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\AdminServiceModerationController@index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
        'as' => 'generated::yYr5Kp9Bdv5hyd6g',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::QZtZzIjAu8ZpEjVx' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/service-submissions/{id}/approve',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:super_admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\AdminServiceModerationController@approve',
        'controller' => 'App\\Http\\Controllers\\Admin\\AdminServiceModerationController@approve',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
        'as' => 'generated::QZtZzIjAu8ZpEjVx',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::WHpWjydUniEcT5RN' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/service-submissions/{id}/reject',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:super_admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\AdminServiceModerationController@reject',
        'controller' => 'App\\Http\\Controllers\\Admin\\AdminServiceModerationController@reject',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
        'as' => 'generated::WHpWjydUniEcT5RN',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::0FJhq5nWUGRRifA4' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/recycle-bin/doctors',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:super_admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\RecycleBinController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\RecycleBinController@index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
        'as' => 'generated::0FJhq5nWUGRRifA4',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::83MFkglBzAcwLl95' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/enquiries',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:super_admin',
        ),
        'uses' => 'App\\Http\\Controllers\\LandingEnquiryController@index',
        'controller' => 'App\\Http\\Controllers\\LandingEnquiryController@index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
        'as' => 'generated::83MFkglBzAcwLl95',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::c7bL5tz9cc3lVFC3' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/modules',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:super_admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\ModuleController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\ModuleController@index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
        'as' => 'generated::c7bL5tz9cc3lVFC3',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::Z3lxBmlsPOwLbpn6' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/subscriptions',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:super_admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\AdminSubscriptionController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\AdminSubscriptionController@index',
        'namespace' => NULL,
        'prefix' => 'admin/subscriptions',
        'where' => 
        array (
        ),
        'as' => 'generated::Z3lxBmlsPOwLbpn6',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::1yDYUotXmvsU7GRT' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/subscriptions/{doctor}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:super_admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\AdminSubscriptionController@show',
        'controller' => 'App\\Http\\Controllers\\Admin\\AdminSubscriptionController@show',
        'namespace' => NULL,
        'prefix' => 'admin/subscriptions',
        'where' => 
        array (
        ),
        'as' => 'generated::1yDYUotXmvsU7GRT',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::Dkyi5TqeKzKUfZaa' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/subscriptions/{doctor}/renew',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:super_admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\AdminSubscriptionController@renew',
        'controller' => 'App\\Http\\Controllers\\Admin\\AdminSubscriptionController@renew',
        'namespace' => NULL,
        'prefix' => 'admin/subscriptions',
        'where' => 
        array (
        ),
        'as' => 'generated::Dkyi5TqeKzKUfZaa',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::2sj9MWwsX7Opxiy0' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/subscriptions/{doctor}/restart',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:super_admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\AdminSubscriptionController@restart',
        'controller' => 'App\\Http\\Controllers\\Admin\\AdminSubscriptionController@restart',
        'namespace' => NULL,
        'prefix' => 'admin/subscriptions',
        'where' => 
        array (
        ),
        'as' => 'generated::2sj9MWwsX7Opxiy0',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::XuEOVVsRMbDcZZLa' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/subscriptions/{doctor}/cancel',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:super_admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\AdminSubscriptionController@cancel',
        'controller' => 'App\\Http\\Controllers\\Admin\\AdminSubscriptionController@cancel',
        'namespace' => NULL,
        'prefix' => 'admin/subscriptions',
        'where' => 
        array (
        ),
        'as' => 'generated::XuEOVVsRMbDcZZLa',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::dlzzabQo9JV9gy2i' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/subscriptions/{doctor}/lifetime',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:super_admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\AdminSubscriptionController@lifetime',
        'controller' => 'App\\Http\\Controllers\\Admin\\AdminSubscriptionController@lifetime',
        'namespace' => NULL,
        'prefix' => 'admin/subscriptions',
        'where' => 
        array (
        ),
        'as' => 'generated::dlzzabQo9JV9gy2i',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::13TeNiQpATFZZdD8' => 
    array (
      'methods' => 
      array (
        0 => 'PATCH',
      ),
      'uri' => 'admin/subscriptions/{doctor}/plan',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'tenant',
          3 => 'require.specialty.admin',
          4 => 'role:super_admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\AdminSubscriptionController@updatePlan',
        'controller' => 'App\\Http\\Controllers\\Admin\\AdminSubscriptionController@updatePlan',
        'namespace' => NULL,
        'prefix' => 'admin/subscriptions',
        'where' => 
        array (
        ),
        'as' => 'generated::13TeNiQpATFZZdD8',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::xkv5x4lweNP7ctGg' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/system-reset',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
          2 => 'role:super_admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\SystemResetController@resetSystemData',
        'controller' => 'App\\Http\\Controllers\\Admin\\SystemResetController@resetSystemData',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'generated::xkv5x4lweNP7ctGg',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::yfD0dgaMguHunPal' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'up',
      'action' => 
      array (
        'uses' => 'O:55:"Laravel\\SerializableClosure\\UnsignedSerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:853:"function () {
                    $exception = null;

                    try {
                        \\Illuminate\\Support\\Facades\\Event::dispatch(new \\Illuminate\\Foundation\\Events\\DiagnosingHealth);
                    } catch (\\Throwable $e) {
                        if (app()->hasDebugModeEnabled()) {
                            throw $e;
                        }

                        report($e);

                        $exception = $e->getMessage();
                    }

                    return response(\\Illuminate\\Support\\Facades\\View::file(\'/Users/apple/Downloads/backup_crm_stable_zip_compressed/api/vendor/laravel/framework/src/Illuminate/Foundation/Configuration\'.\'/../resources/health-up.blade.php\', [
                        \'exception\' => $exception,
                    ]), status: $exception ? 500 : 200);
                }";s:5:"scope";s:54:"Illuminate\\Foundation\\Configuration\\ApplicationBuilder";s:4:"this";N;s:4:"self";s:32:"000000000000089c0000000000000000";}}',
        'as' => 'generated::yfD0dgaMguHunPal',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::MzzVbpH3F8EdaeKR' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '/',
      'action' => 
      array (
        'uses' => 'O:55:"Laravel\\SerializableClosure\\UnsignedSerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:694:"function () {
                return \\response()->json([
                    "name" => "BioOrganicCare API",
                    "version" => "2.1",
                    "status" => "running",
                    "timestamp" => \\now(),
                    "environment" => \\app()->environment(),
                    "endpoints" => [
                        "health" => "/api/health",
                        "login" => "/api/login",
                        "metrics" => "/api/metrics",
                        "status" => "/api/status",
                        "monitor" => "/api/monitor",
                        "system" => "/api/system"
                    ]
                ]);
            }";s:5:"scope";s:47:"Illuminate\\Foundation\\Console\\RouteCacheCommand";s:4:"this";N;s:4:"self";s:32:"00000000000008990000000000000000";}}',
        'as' => 'generated::MzzVbpH3F8EdaeKR',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::aZDF0KhAMcA3AqcQ' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'health',
      'action' => 
      array (
        'uses' => 'O:55:"Laravel\\SerializableClosure\\UnsignedSerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:164:"function() {
                return \\response()->json([
                    \'status\' => \'ok\',
                    \'time\' => \\now()
                ]);
            }";s:5:"scope";s:47:"Illuminate\\Foundation\\Console\\RouteCacheCommand";s:4:"this";N;s:4:"self";s:32:"000000000000080d0000000000000000";}}',
        'as' => 'generated::aZDF0KhAMcA3AqcQ',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::VXgueQIaXWscpffp' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'status',
      'action' => 
      array (
        'uses' => 'O:55:"Laravel\\SerializableClosure\\UnsignedSerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:412:"function () {
                return \\response()->json([
                    "api" => "OK",
                    "database" => \\Illuminate\\Support\\Facades\\DB::connection()->getPdo() ? "OK" : "FAIL",
                    "storage" => \\is_writable(\\storage_path()) ? "OK" : "FAIL",
                    "time" => \\now(),
                    "version" => \\config(\'app.version\', \'2.1\')
                ]);
            }";s:5:"scope";s:47:"Illuminate\\Foundation\\Console\\RouteCacheCommand";s:4:"this";N;s:4:"self";s:32:"00000000000008970000000000000000";}}',
        'as' => 'generated::VXgueQIaXWscpffp',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::Fm3clMLMTcai6NUh' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'metrics',
      'action' => 
      array (
        'uses' => 'O:55:"Laravel\\SerializableClosure\\UnsignedSerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:432:"function () {
                return \\response()->json([
                    "memory_usage_mb" => \\round(\\memory_get_usage(true) / 1024 / 1024, 2),
                    "memory_peak_mb" => \\round(\\memory_get_peak_usage(true) / 1024 / 1024, 2),
                    "php_version" => \\phpversion(),
                    "laravel_version" => \\app()->version(),
                    "server_time" => \\now()
                ]);
            }";s:5:"scope";s:47:"Illuminate\\Foundation\\Console\\RouteCacheCommand";s:4:"this";N;s:4:"self";s:32:"00000000000008030000000000000000";}}',
        'as' => 'generated::Fm3clMLMTcai6NUh',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::bjjiggf0OHqIJFqh' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'monitor',
      'action' => 
      array (
        'uses' => 'O:55:"Laravel\\SerializableClosure\\UnsignedSerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:570:"function () {
                try {
                    \\Illuminate\\Support\\Facades\\DB::connection()->getPdo();
                    return \\response()->json([
                        "status" => "healthy",
                        "database" => "connected",
                        "time" => \\now()
                    ]);
                } catch (\\Throwable $e) {
                    return \\response()->json([
                        "status" => "failure",
                        "error" => $e->getMessage()
                    ], 500);
                }
            }";s:5:"scope";s:47:"Illuminate\\Foundation\\Console\\RouteCacheCommand";s:4:"this";N;s:4:"self";s:32:"00000000000008010000000000000000";}}',
        'as' => 'generated::bjjiggf0OHqIJFqh',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::rNHCyuHiBPK9L6bc' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'system',
      'action' => 
      array (
        'uses' => 'O:55:"Laravel\\SerializableClosure\\UnsignedSerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:298:"function () {
                return \\response()->json([
                    "laravel" => \\app()->version(),
                    "php" => \\phpversion(),
                    "environment" => \\app()->environment(),
                    "debug" => \\config(\'app.debug\')
                ]);
            }";s:5:"scope";s:47:"Illuminate\\Foundation\\Console\\RouteCacheCommand";s:4:"this";N;s:4:"self";s:32:"00000000000007ff0000000000000000";}}',
        'as' => 'generated::rNHCyuHiBPK9L6bc',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'storage.local' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'storage/{path}',
      'action' => 
      array (
        'uses' => 'O:55:"Laravel\\SerializableClosure\\UnsignedSerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:3:{s:4:"disk";s:5:"local";s:6:"config";a:5:{s:6:"driver";s:5:"local";s:4:"root";s:79:"/Users/apple/Downloads/backup_crm_stable_zip_compressed/api/storage/app/private";s:5:"serve";b:1;s:5:"throw";b:0;s:6:"report";b:0;}s:12:"isProduction";b:0;}s:8:"function";s:323:"function (\\Illuminate\\Http\\Request $request, string $path) use ($disk, $config, $isProduction) {
                    return (new \\Illuminate\\Filesystem\\ServeFile(
                        $disk,
                        $config,
                        $isProduction
                    ))($request, $path);
                }";s:5:"scope";s:47:"Illuminate\\Filesystem\\FilesystemServiceProvider";s:4:"this";N;s:4:"self";s:32:"00000000000007eb0000000000000000";}}',
        'as' => 'storage.local',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
        'path' => '.*',
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
  ),
)
);
