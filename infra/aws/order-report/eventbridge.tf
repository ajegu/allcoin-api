resource "aws_cloudwatch_event_rule" "order_report" {
    name = "${var.app_name}-${var.lambda_name}EventRule"
    description = "Report orders every day"
    schedule_expression = "rate(1 day)"
}

resource "aws_cloudwatch_event_target" "order_report" {
    arn = aws_lambda_function.order_report.arn
    rule = aws_cloudwatch_event_rule.order_report.name
}

