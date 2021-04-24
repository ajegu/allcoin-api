resource "aws_cloudwatch_event_rule" "binance_price_analyzer" {
    name = "${var.app_name}-${var.lambda_name}EventRule"
    description = "Sync Binance price every minutes"
    schedule_expression = "rate(1 minute)"
}

resource "aws_cloudwatch_event_target" "binance_price_analyzer" {
    arn = aws_lambda_function.binance_price_analyzer.arn
    rule = aws_cloudwatch_event_rule.binance_price_analyzer.name
}

