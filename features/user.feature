Feature: Manage bbPress Users

  Scenario: User CRUD operations
    Given a bbPress install

    When I run `wp user create testuser2 testuser2@example.com --porcelain`
    Then STDOUT should be a number
    And save STDOUT as {MEMBER_ID}

    When I run `wp bbp user set-role --user-id={MEMBER_ID} --role=moderator`
    Then STDOUT should contain:
      """
      Success: New role for user set successfully.
      """

    When I run `wp bbp user spam {MEMBER_ID}`
    Then STDOUT should contain:
      """
      Success: User topics and replies marked as spam.
      """

    When I run `wp bbp user ham {MEMBER_ID}`
    Then STDOUT should contain:
      """
      Success: User topics and replies marked as ham.
      """
