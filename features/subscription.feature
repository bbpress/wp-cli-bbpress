Feature: Manage bbPress subscriptions

  Background:
    Given a WP install

  Scenario: Add a user subscription.
    When I run `wp bbp subscription add --user-id=5465 --object-id=65476`
    Then STDOUT should contain:
      """
      Success: Subscription successfully added.
      """

  Scenario: Remove a user subscription.
    When I run `wp bbp subscription remove --user-id=5465 --object-id=65476`
    Then STDOUT should contain:
      """
      Success: Subscription successfully removed.
      """

  Scenario: List users who subscribed to an object.
    When I run `wp bbp subscription list_users --object-id=242`
    Then STDOUT should contain:
      """
      54564 4564 454 545
      """
