Feature: Manage bbPress Tools

  Scenario: Testing bbPress tools commands
    Given a bbPress install

    When I run `wp bbp tool repair --type=topic-reply-count`
    Then STDOUT should contain:
      """
      Success: Counting the number of replies in each topic&hellip; Complete!
      """

    When I run `wp bbp tool upgrade --type=user-engagements`
    Then STDOUT should contain:
      """
      Success: Error: Upgrading user engagements&hellip; No engagements to upgrade.
      """

    When I run `wp bbp tool reset --yes`
    Then STDOUT should contain:
      """
      Success: bbPress reset.
      """
