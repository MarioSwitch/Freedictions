# Changelog
## Overview
| Version | First commit               | Last commit              | Link                                                                     |
| ------- | -------------------------- | ------------------------ | ------------------------------------------------------------------------ |
| 3       | 3.0.0-alpha1<br>2024-11-07 | *See v3 branch*          | [Branch: v3](https://github.com/MarioSwitch/Freedictions/tree/v3)        |
| 2.1     | 2.1.alpha1<br>2024-08-22   | 2.1.65<br>2025-09-30     | [Tag: v2.1.65](https://github.com/MarioSwitch/Freedictions/tree/v2.1.65) |
| 2.0     | Commit #29<br>2023-05-30   | 2.0.57<br>2024-05-31     | [Tag: v2.0.57](https://github.com/MarioSwitch/Freedictions/tree/v2.0.57) |
| 1.1     | Commit #8<br>2023-04-28    | Commit #28<br>2023-05-09 | [Tag: v1.1](https://github.com/MarioSwitch/Freedictions/tree/v1.1)       |
| 1.0     | Commit #1<br>2022-06-02    | Commit #7<br>2022-08-22  | [Tag: v1.0](https://github.com/MarioSwitch/Freedictions/tree/v1.0)       |

Latest version is available on the ["Code" page](https://github.com/MarioSwitch/Freedictions) of the GitHub project.

Legacy versions (at their last revision) are available on the ["Tags" page](https://github.com/MarioSwitch/Freedictions/tags) of the GitHub project.

## Details
### Version 3
Version 3 development started on November 7, 2024 and was published for the first time on December 10, 2024.

Notable changes since v2.1:
- Project renaming (*Better Twitch Predictions* is now *Freedictions*)
- UI revamp (icons, font, colors)
- Usage of RewriteRules for lighter URLs (e.g. ``/index.php?view=prediction&id=42`` is now ``/prediction/42``)
- Usage of Weblate to manage translations
- Data revamp (deleting useless stats/pages and achievements)

### Versions 2.x
#### Version 2.1
Version 2.1 was initially developed to implement a complete translation system, by replacing all strings with keys and creating ``strings/*.json`` that contains corresponding texts for each language.

There were 3 development cycles:
1. **Alpha** from August 22 (``2.1.alpha1``) to September 2 (``2.1.alpha16``), 2024
2. **Beta** from September 2 (``2.1.beta17``) to 11 (``2.1.beta27``), 2024
3. **Stable** from September 13, 2024 (``2.1.28``) to September 30, 2025 (``2.1.65``)

Notable changes since v2.0:
- Replacing raw strings by translation keys
- New "settings" page (language, shorten large numbers, time format)
- Additional achievements
- Site statistics

#### Version 2.0
Version 2.0 needed a complete code rewriting to implement core fixes and additions.

There were 3 development cycles:
1. **Beta** from May 30 (commit #29) to June 1 (commit #43), 2023. On this date, the project became public for the first time.
2. **Stable without version numbering** from June 5 (commit #44) to December 18 (commit #60), 2023
3. **Stable with version numbering** from January 3 (``2.0.33``) to May 31 (``2.0.57``), 2024

Notable changes since v1.1:
- Allowing creators to bet on their own predictions
- Allowing users to add points to their bets
- Shorting numbers above 1 million (1,000,000 is now 1.00M)
- New "all users" and "all predictions" pages
- New achievements system
- Additional user statistics

### Versions 1.x
#### Version 1.1
Version 1.1 was an upgrade of the initial release by implementing new features that we had no time to before school presentation.

Notable changes since v1.0:
- Replacing UTC timestamps by relative time left/elapsed
- New "leaderboard" page with all users sorted by points
- Allowing users to see profile of other users, change their password and delete their account and their resolved predictions
- Fixing a HUGE number of bugs and security issues

#### Version 1.0
Version 1.0 was the one developed for a first-year software engineering project. This started on May 5 and was presented to the jury on June 3, 2022. We had a grade of 12 out of 20 (French scale).
