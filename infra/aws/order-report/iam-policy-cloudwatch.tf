resource "aws_iam_policy" "order_report_logs" {
    name = "${var.app_name}-${var.lambda_name}IAMPolicyLogs"

    path = "/"
    description = "IAM policy for logging from a lambda"

    policy = <<EOF
{
  "Version": "2012-10-17",
  "Statement": [
    {
      "Action": [
        "logs:CreateLogGroup",
        "logs:CreateLogStream",
        "logs:PutLogEvents"
      ],
      "Resource": "arn:aws:logs:*:*:*",
      "Effect": "Allow"
    }
  ]
}
EOF
}

resource "aws_iam_role_policy_attachment" "policy_attachment_order_report_logs" {
    role = aws_iam_role.order_report.name
    policy_arn = aws_iam_policy.order_report_logs.arn
}
