resource "aws_iam_policy" "order_report_dynamodb" {
    name = "${var.app_name}-${var.lambda_name}DynamoDb"

    path = "/"
    description = "IAM policy for DynamoDb from a lambda"

    policy = <<EOF
{
  "Version": "2012-10-17",
  "Statement": [
    {
      "Action": [
        "dynamodb:Query",
        "dynamodb:PutItem"
      ],
      "Resource": "arn:aws:dynamodb:*:*:*",
      "Effect": "Allow"
    }
  ]
}
EOF
}

resource "aws_iam_role_policy_attachment" "policy_attachment_order_report_dynamodb" {
    role = aws_iam_role.order_report.name
    policy_arn = aws_iam_policy.order_report_dynamodb.arn
}

