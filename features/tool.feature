Feature: Manage bbPress Tools

  Scenario: Testing bbPress tools commands
    Given a bbPress install

    When I run `wp bbp tool repair --type=topic-reply-count`
    Then STDOUT should contain:
      """
      Success: Counting the number of replies in each topic&hellip; Complete!
      """
