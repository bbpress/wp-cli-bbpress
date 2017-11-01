Feature: Manage bbPress Forums

 Scenario: Delete a forum
    Given a WP install

    When I run `wp bbp forum delete 520`
    Then STDOUT should contain:
      """
      Success: Forum and its topics and replies deleted.
      """
