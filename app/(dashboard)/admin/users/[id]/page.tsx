"use client"

import { useEffect, use } from "react"
import { useRouter } from "next/navigation"
import Link from "next/link"
import useSWR from "swr"
import { useAuth } from "@/lib/auth"
import { getUserById } from "@/lib/api"
import { formatDate, getInitials } from "@/lib/utils"
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@/components/ui/card"
import { Badge } from "@/components/ui/badge"
import { Button } from "@/components/ui/button"
import { Avatar, AvatarFallback } from "@/components/ui/avatar"
import {
  ArrowLeft,
  Mail,
  Calendar,
  Clock,
  Activity,
  Shield,
  Lock,
  Loader2,
  ShieldAlert,
} from "lucide-react"

export default function UserDetailPage({
  params,
}: {
  params: Promise<{ id: string }>
}) {
  const { id } = use(params)
  const { hasRole, isLoading: authLoading } = useAuth()
  const router = useRouter()
  const isAdmin = hasRole("admin")
  const userId = parseInt(id, 10)

  const { data, isLoading, error } = useSWR(
    isAdmin && userId ? `user-${userId}` : null,
    () => getUserById(userId),
    { revalidateOnFocus: false }
  )

  useEffect(() => {
    if (!authLoading && !isAdmin) {
      router.replace("/dashboard")
    }
  }, [authLoading, isAdmin, router])

  if (!isAdmin) {
    return (
      <div className="flex flex-col items-center justify-center py-20 gap-4">
        <ShieldAlert className="h-12 w-12 text-muted-foreground" />
        <p className="text-muted-foreground">
          You do not have permission to view this page.
        </p>
      </div>
    )
  }

  if (isLoading) {
    return (
      <div className="flex items-center justify-center py-20">
        <Loader2 className="h-8 w-8 animate-spin text-muted-foreground" />
      </div>
    )
  }

  if (error || !data?.data) {
    return (
      <div className="flex flex-col items-center justify-center py-20 gap-4">
        <p className="text-muted-foreground">Failed to load user details.</p>
        <Link href="/admin">
          <Button variant="outline">
            <ArrowLeft className="h-4 w-4 mr-2" />
            Back to Users
          </Button>
        </Link>
      </div>
    )
  }

  const user = data.data.user
  const permissions = data.data.permissions
  const userRoles = user.roles || []

  const details = [
    { label: "Email", value: user.email, icon: Mail },
    {
      label: "Status",
      value: user.is_active ? "Active" : "Inactive",
      icon: Activity,
    },
    {
      label: "Member since",
      value: formatDate(user.created_at),
      icon: Calendar,
    },
    {
      label: "Last login",
      value: formatDate(user.last_login),
      icon: Clock,
    },
  ]

  return (
    <div className="flex flex-col gap-6 max-w-3xl">
      {/* Back button */}
      <Link href="/admin">
        <Button variant="ghost" className="gap-2 -ml-2">
          <ArrowLeft className="h-4 w-4" />
          Back to Users
        </Button>
      </Link>

      {/* User header card */}
      <Card>
        <CardContent className="p-6">
          <div className="flex items-center gap-4">
            <Avatar className="h-16 w-16">
              <AvatarFallback className="bg-primary/10 text-primary text-lg font-bold">
                {getInitials(user.name)}
              </AvatarFallback>
            </Avatar>
            <div className="flex flex-col gap-1">
              <h1 className="text-2xl font-bold">{user.name}</h1>
              <p className="text-sm text-muted-foreground">{user.email}</p>
              <div className="flex gap-2 mt-1">
                {userRoles.map((role: string) => (
                  <Badge
                    key={role}
                    variant={role === "admin" ? "default" : "secondary"}
                    className="capitalize"
                  >
                    {role}
                  </Badge>
                ))}
                <Badge
                  variant={user.is_active ? "outline" : "destructive"}
                  className="text-xs"
                >
                  {user.is_active ? "Active" : "Inactive"}
                </Badge>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      {/* Details */}
      <Card>
        <CardHeader>
          <div className="flex items-center gap-2">
            <Shield className="h-5 w-5 text-primary" />
            <CardTitle className="text-lg">Account Details</CardTitle>
          </div>
          <CardDescription>User account information</CardDescription>
        </CardHeader>
        <CardContent>
          <div className="grid gap-4 sm:grid-cols-2">
            {details.map((detail) => (
              <div
                key={detail.label}
                className="flex items-start gap-3 rounded-lg border border-border p-3"
              >
                <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-muted">
                  <detail.icon className="h-4 w-4 text-muted-foreground" />
                </div>
                <div className="flex flex-col min-w-0">
                  <p className="text-xs text-muted-foreground">
                    {detail.label}
                  </p>
                  <p className="text-sm font-medium truncate">
                    {detail.value || "N/A"}
                  </p>
                </div>
              </div>
            ))}
          </div>
        </CardContent>
      </Card>

      {/* Permissions */}
      <Card>
        <CardHeader>
          <div className="flex items-center gap-2">
            <Lock className="h-5 w-5 text-accent" />
            <CardTitle className="text-lg">Permissions</CardTitle>
          </div>
          <CardDescription>
            Granular permissions granted through roles
          </CardDescription>
        </CardHeader>
        <CardContent>
          {permissions.length > 0 ? (
            <div className="grid gap-2 sm:grid-cols-2">
              {permissions.map((perm) => {
                const permName =
                  typeof perm === "string" ? perm : perm.name
                const permDescription =
                  typeof perm === "string" ? null : perm.description
                const [category, action] = permName.split(".")
                return (
                  <div
                    key={permName}
                    className="flex items-center gap-2 rounded-lg border border-border px-3 py-2"
                  >
                    <span className="text-xs font-mono text-muted-foreground bg-muted px-1.5 py-0.5 rounded shrink-0">
                      {category}
                    </span>
                    <div className="flex flex-col min-w-0">
                      <span className="text-sm font-medium capitalize">
                        {action}
                      </span>
                      {permDescription && (
                        <span className="text-xs text-muted-foreground truncate">
                          {permDescription}
                        </span>
                      )}
                    </div>
                  </div>
                )
              })}
            </div>
          ) : (
            <p className="text-sm text-muted-foreground">
              No permissions assigned
            </p>
          )}
        </CardContent>
      </Card>
    </div>
  )
}
