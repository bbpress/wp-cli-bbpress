Feature: Manage bbPress subscriptions

  Background:
    Given a WP install

  Scenario: Add a topic to user's favorites.
    When I run `wp bbp favorite add --user-id=5465 --topic-id=65476`
    Then STDOUT should contain:
      """
      Success: Favorite successfully added.
      """

  Scenario: Remove a topic from user's favorites.
    When I run `wp bbp favorite remove --user-id=5465 --topic-id=65476`
    Then STDOUT should contain:
      """
      Success: Favorite successfully removed.
      """

  Scenario: List users who favorited a topic.
    When I run `wp bbp favorite list_users 456`
    Then STDOUT should contain:
      """
      54564 4564 454 545
      """
