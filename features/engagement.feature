Feature: Manage bbPress engagements

  Background:
    Given a WP install

  Scenario: Add a topic to user's engagements.
    When I run `wp bbp engagement add --user-id=5465 --topic-id=65476`
    Then STDOUT should contain:
      """
      Success: Engagement successfully added.
      """

  Scenario: Remove a topic from user's engagements.
    When I run `wp bbp engagement remove --user-id=5465 --topic-id=65476`
    Then STDOUT should contain:
      """
      Success: Engagement successfully removed.
      """

  Scenario: Recalculate all of the users who have engaged in a topic.
    When I run `wp bbp engagement recalculate 132`
    Then STDOUT should contain:
      """
      Success: Engagements successfully recalculated.
      """

  Scenario: List the users who have engaged in a topic.
    When I run `wp bbp subscription list_users --object-id=242`
    Then STDOUT should contain:
      """
      54564 4564 454 545
      """
