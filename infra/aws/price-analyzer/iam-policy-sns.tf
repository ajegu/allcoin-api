resource "aws_iam_policy" "price_analyzer_sns" {
    name = "${var.app_name}${var.lambda_name}Sns"

    path = "/"
    description = "IAM policy for Sns from a lambda"

    policy = <<EOF
{
  "Version": "2012-10-17",
  "Statement": [
    {
      "Action": [
        "sns:Publish"
      ],
      "Resource": "${aws_sns_topic.price_analyzer.arn}",
      "Effect": "Allow"
    }
  ]
}
EOF
}

resource "aws_iam_role_policy_attachment" "policy_attachment_price_analyzer_sns" {
    role = aws_iam_role.price_analyzer.name
    policy_arn = aws_iam_policy.price_analyzer_sns.arn
}

