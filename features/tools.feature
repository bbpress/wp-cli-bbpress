Feature: Manage bbPress Tools (Repair/Upgrade/Reset/Converters)

  Scenario: bbPress repair
    Given a WP install

    When I run `wp bbp tools repair --type=topic-reply-count`
    Then STDOUT should contain:
      """
      
      """

  Scenario: bbPress upgrade
    Given a WP install

    When I run `wp bbp tools upgrade --type=user-engagements`
    Then STDOUT should contain:
      """
      
      """

  Scenario: bbPress reset
    Given a WP install

    When I run `wp bbp tools reset --yes`
    Then STDOUT should contain:
      """
      Sucess: bbPress reset.
      """

  Scenario: List bbPress converters
    Given a WP install

    When I run `wp bbp tools list_converters`
    Then STDOUT should contain:
      """
      
      """