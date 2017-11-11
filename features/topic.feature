Feature: Manage bbPress Topics

	Scenario: Topic CRUD commands
    Given a bbPress install

    When I run `wp bbp topic create --title="Topic" --porcelain`
    Then STDOUT should be a number
    And save STDOUT as {TOPIC_ID}

    When I run `wp bbp topic list --format=ids`
    Then STDOUT should contain:
      """
      {TOPIC_ID}
      """

		When I run `wp bbp topic trash {TOPIC_ID}`
		Then STDOUT should contain:
			"""
			Success: Topic {TOPIC_ID} successfully trashed.
			"""

		When I run `wp bbp topic untrash {TOPIC_ID}`
		Then STDOUT should contain:
			"""
			Success: Topic {TOPIC_ID} successfully untrashed.
			"""

		When I run `wp bbp topic delete {TOPIC_ID} --yes`
		Then STDOUT should contain:
			"""
			Success: Topic {TOPIC_ID} successfully deleted.
			"""

		When I try `wp bbp topic delete {TOPIC_ID} --yes`
    	Then the return code should be 1

  Scenario: Testing Close/Open Commands
    Given a bbPress install

    When I run `wp bbp topic create --title="Topic" --porcelain`
    Then STDOUT should be a number
    And save STDOUT as {TOPIC_ID}

    When I run `wp bbp topic close {TOPIC_ID}`
    Then STDOUT should contain:
      """
      Success: Topic {TOPIC_ID} successfully closed.
      """

    When I run `wp bbp topic open {TOPIC_ID}`
    Then STDOUT should contain:
      """
      Success: Topic {TOPIC_ID} successfully opened.
      """
