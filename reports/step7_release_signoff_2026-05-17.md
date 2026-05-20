# Step 7 Release Sign-Off (2026-05-17)

## Scope
This sign-off captures final migration stabilization for the live ACP Demo portal after CakePHP 4 compatibility fixes and runtime regressions.

## Critical Files To Keep Deployed
- config/bootstrap.php
- src/Controller/Admin/AdminsController.php
- src/Controller/Admin/JudgeevaluationsController.php
- src/Controller/Admin/EventsubmissionsController.php
- src/Model/Table/JudgingAssignmentsTable.php
- src/Template/Element/Admin/Judgeevaluations/index.php

## Verified Fix Points
- src/Controller/Admin/AdminsController.php:62
- src/Controller/Admin/AdminsController.php:110
- src/Controller/Admin/JudgeevaluationsController.php:35
- src/Controller/Admin/EventsubmissionsController.php:88
- src/Template/Element/Admin/Judgeevaluations/index.php:103
- config/bootstrap.php:94
- src/Model/Table/JudgingAssignmentsTable.php:8

## Live Runtime Verification
PASS
- /acp_demo/
- /acp_demo/users/login
- /acp_demo/admin
- /acp_demo/admin/admins/dashboard
- /acp_demo/admin/judgeevaluations
- /acp_demo/admin/eventsubmissions
- /acp_demo/admin/users
- /acp_demo/admin/users/judges
- /acp_demo/admin/conventions
- /acp_demo/admin/conventionregistrations
- /acp_demo/admin/conventionregistrations/alljudges
- /acp_demo/admin/transactions
- /acp_demo/admin/events
- /acp_demo/admin/seasons
- /acp_demo/admin/divisions
- /acp_demo/admin/judgeevaluations/judgedetail/8536

## Known Non-Blocking Noise
External 403 console noise from security vendor endpoint:
- https://s3-us-west-2.amazonaws.com/mfesecure-public/host/convention.accelerateministries.com.au/client.json?source=jsmain
- https://s3-us-west-2.amazonaws.com/mfesecure-public/host/convention.accelerateministries.com.au/client.json?source=jsinline

No app-side fix required for these two URLs.

## Rollback Notes
If needed, rollback only these files first:
- src/Controller/Admin/EventsubmissionsController.php
- src/Controller/Admin/JudgeevaluationsController.php
- src/Controller/Admin/AdminsController.php
- src/Model/Table/JudgingAssignmentsTable.php
- src/Template/Element/Admin/Judgeevaluations/index.php
- config/bootstrap.php

Then clear Cake cache directories under tmp/cache and re-test /admin routes.

## Sign-Off Status
Step 7 release validation: COMPLETE
