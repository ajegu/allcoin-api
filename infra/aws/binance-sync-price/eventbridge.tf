resource "aws_cloudwatch_event_rule" "binance_sync_price" {
    name = "${var.app_name}-${var.lambda_name}EventRule"
    description = "Get Binance price every minutes"
    schedule_expression = "rate(1 minute)"
}

resource "aws_cloudwatch_event_target" "binance_sync_price" {
    arn = aws_lambda_function.binance_sync_price.arn
    rule = aws_cloudwatch_event_rule.binance_sync_price.name
}

