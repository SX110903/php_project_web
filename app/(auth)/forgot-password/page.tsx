"use client"

import { useState } from "react"
import Link from "next/link"
import { useForm } from "react-hook-form"
import { zodResolver } from "@hookform/resolvers/zod"
import { z } from "zod"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import {
  Card,
  CardContent,
  CardDescription,
  CardFooter,
  CardHeader,
  CardTitle,
} from "@/components/ui/card"
import { Shield, Loader2, ArrowLeft, Mail } from "lucide-react"

const forgotSchema = z.object({
  email: z.string().email("Enter a valid email address"),
})

type ForgotForm = z.infer<typeof forgotSchema>

export default function ForgotPasswordPage() {
  const [submitted, setSubmitted] = useState(false)

  const {
    register,
    handleSubmit,
    formState: { errors, isSubmitting },
  } = useForm<ForgotForm>({
    resolver: zodResolver(forgotSchema),
  })

  const onSubmit = async (_data: ForgotForm) => {
    // Simulate API call - backend would handle sending email
    await new Promise((resolve) => setTimeout(resolve, 1000))
    setSubmitted(true)
  }

  return (
    <>
      <div className="flex items-center gap-2 mb-8 lg:hidden">
        <Shield className="w-6 h-6 text-primary" />
        <span className="text-xl font-bold">Secure App</span>
      </div>

      <Card className="border-border/50 shadow-sm">
        {submitted ? (
          <>
            <CardHeader className="text-center pb-4">
              <div className="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-primary/10 mb-4">
                <Mail className="h-6 w-6 text-primary" />
              </div>
              <CardTitle className="text-2xl font-bold">
                Check your email
              </CardTitle>
              <CardDescription>
                If an account exists with that email, we sent password reset
                instructions.
              </CardDescription>
            </CardHeader>
            <CardFooter className="justify-center">
              <Link
                href="/login"
                className="text-sm text-primary font-medium hover:underline inline-flex items-center gap-1"
              >
                <ArrowLeft className="w-4 h-4" />
                Back to sign in
              </Link>
            </CardFooter>
          </>
        ) : (
          <>
            <CardHeader className="pb-4">
              <CardTitle className="text-2xl font-bold">
                Forgot password
              </CardTitle>
              <CardDescription>
                {"Enter your email and we'll send you a reset link"}
              </CardDescription>
            </CardHeader>
            <CardContent>
              <form
                onSubmit={handleSubmit(onSubmit)}
                className="flex flex-col gap-4"
              >
                <div className="flex flex-col gap-2">
                  <Label htmlFor="email">Email</Label>
                  <Input
                    id="email"
                    type="email"
                    placeholder="you@example.com"
                    autoComplete="email"
                    {...register("email")}
                  />
                  {errors.email && (
                    <p className="text-sm text-destructive">
                      {errors.email.message}
                    </p>
                  )}
                </div>

                <Button type="submit" disabled={isSubmitting} className="mt-2">
                  {isSubmitting ? (
                    <>
                      <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                      Sending...
                    </>
                  ) : (
                    "Send reset link"
                  )}
                </Button>
              </form>
            </CardContent>
            <CardFooter className="justify-center">
              <Link
                href="/login"
                className="text-sm text-muted-foreground hover:text-foreground inline-flex items-center gap-1"
              >
                <ArrowLeft className="w-4 h-4" />
                Back to sign in
              </Link>
            </CardFooter>
          </>
        )}
      </Card>
    </>
  )
}
