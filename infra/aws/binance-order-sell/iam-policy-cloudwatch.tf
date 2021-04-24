resource "aws_iam_policy" "binance_order_sell_logs" {
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

resource "aws_iam_role_policy_attachment" "policy_attachment_binance_order_sell_logs" {
    role = aws_iam_role.binance_order_sell.name
    policy_arn = aws_iam_policy.binance_order_sell_logs.arn
}
