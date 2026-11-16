# Permissions
Starting on ``v3.0.100-alpha`` (July 28, 2026), there are 4 permission levels (``mod`` attribute from 0 to 3). Below are the detailed permissions for each level, as of ``v3.0.111-alpha`` (October 2, 2026).

## Table
| Level (``mod``)            | Default (0) | Verifier (1) | Moderator (2) | Administrator (3) |
| -------------------------- | ----------- | ------------ | ------------- | ----------------- |
| Color                      | Black       | Brown        | Green         | Red               |
| User: change password      | 👤           | 👤            | 🟤             | ✅                 |
| User: delete               | 👤           | 👤            | 🟤             | ✅                 |
| User: edit                 | ❌           | ❌            | ❌             | ✅                 |
| Modqueue: access           | ❌           | ✅            | ✅             | ✅                 |
| Modqueue: actions          | ❌           | ✅            | ✅             | ✅                 |
| Predictions: close         | ❌           | 🟤            | 🟢             | ✅                 |
| Predictions: resolve       | ❌           | 🟤            | 🟢             | ✅                 |
| Predictions: edit          | ❌           | ❌            | 🟤             | ✅                 |
| Predictions: delete        | ❌           | ❌            | 🟤             | ✅                 |
| Predictions: skip approval | ❌           | ✅            | ✅             | ✅                 |

## Meanings
- ❌: No permission (even for their own account/predictions)
- 👤: Permission only for their own account/predictions
- 🟤: Permission only up to verifiers (i.e. (predictions created by) verifiers and default accounts)
- 🟢: Permission only up to moderators (i.e. (predictions created by) moderators, verifiers, and default accounts)
- ✅: Full permission (i.e. all accounts/predictions)

Exact implementation is available in ``functions.php``, ``function isAuthorized``.
