Feature: Manage bbPress Tools

  Scenario: bbPress repair
    Given a WP install

    When I run `wp bbp tools repair --type=topic-reply-count`
    Then STDOUT should contain:
      """
      Success: Counting the number of replies in each topic&hellip; Complete!
      """

  Scenario: bbPress upgrade
    Given a WP install

    When I run `wp bbp tools upgrade --type=user-engagements`
    Then STDOUT should contain:
      """
      Success: Upgrading user engagements&hellip; Complete! 10 engagements upgraded.
      """

  Scenario: bbPress reset
    Given a WP install

    When I run `wp bbp tools reset --yes`
    Then STDOUT should contain:
      """
      Sucess: bbPress reset.
      """
