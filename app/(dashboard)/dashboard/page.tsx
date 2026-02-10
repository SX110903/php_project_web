"use client"

import { useAuth } from "@/lib/auth"
import { formatDate } from "@/lib/utils"
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@/components/ui/card"
import { Badge } from "@/components/ui/badge"
import { Separator } from "@/components/ui/separator"
import {
  ShieldCheck,
  Clock,
  KeyRound,
  UserCheck,
  Activity,
  Lock,
} from "lucide-react"

export default function DashboardPage() {
  const { user, roles, permissions } = useAuth()

  const stats = [
    {
      label: "Account Status",
      value: user?.is_active ? "Active" : "Inactive",
      icon: Activity,
      color: user?.is_active
        ? "text-primary"
        : "text-destructive",
      bgColor: user?.is_active
        ? "bg-primary/10"
        : "bg-destructive/10",
    },
    {
      label: "Last Access",
      value: user?.last_login ? formatDate(user.last_login) : "Never",
      icon: Clock,
      color: "text-accent",
      bgColor: "bg-accent/10",
    },
    {
      label: "Roles",
      value: String(roles.length),
      icon: ShieldCheck,
      color: "text-primary",
      bgColor: "bg-primary/10",
    },
    {
      label: "Permissions",
      value: String(permissions.length),
      icon: KeyRound,
      color: "text-accent",
      bgColor: "bg-accent/10",
    },
  ]

  return (
    <div className="flex flex-col gap-6">
      {/* Page header */}
      <div>
        <h1 className="text-2xl font-bold tracking-tight">Dashboard</h1>
        <p className="text-muted-foreground mt-1">
          Welcome back, {user?.name || "User"}
        </p>
      </div>

      {/* Stats grid */}
      <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        {stats.map((stat) => (
          <Card key={stat.label}>
            <CardContent className="p-5">
              <div className="flex items-center justify-between">
                <div className="flex flex-col gap-1">
                  <p className="text-sm text-muted-foreground">{stat.label}</p>
                  <p className="text-xl font-bold">{stat.value}</p>
                </div>
                <div
                  className={`flex h-10 w-10 items-center justify-center rounded-lg ${stat.bgColor}`}
                >
                  <stat.icon className={`h-5 w-5 ${stat.color}`} />
                </div>
              </div>
            </CardContent>
          </Card>
        ))}
      </div>

      {/* Roles & Permissions */}
      <div className="grid gap-6 lg:grid-cols-2">
        {/* Roles */}
        <Card>
          <CardHeader>
            <div className="flex items-center gap-2">
              <UserCheck className="h-5 w-5 text-primary" />
              <CardTitle className="text-lg">Your Roles</CardTitle>
            </div>
            <CardDescription>
              Assigned roles that define your access level
            </CardDescription>
          </CardHeader>
          <CardContent>
            {roles.length > 0 ? (
              <div className="flex flex-wrap gap-2">
                {roles.map((role) => (
                  <Badge
                    key={role}
                    variant={role === "admin" ? "default" : "secondary"}
                    className="capitalize text-sm px-3 py-1"
                  >
                    {role}
                  </Badge>
                ))}
              </div>
            ) : (
              <p className="text-sm text-muted-foreground">
                No roles assigned
              </p>
            )}
          </CardContent>
        </Card>

        {/* Permissions */}
        <Card>
          <CardHeader>
            <div className="flex items-center gap-2">
              <Lock className="h-5 w-5 text-accent" />
              <CardTitle className="text-lg">Your Permissions</CardTitle>
            </div>
            <CardDescription>
              Granular permissions granted by your roles
            </CardDescription>
          </CardHeader>
          <CardContent>
            {permissions.length > 0 ? (
              <div className="flex flex-col gap-2">
                {permissions.map((perm) => {
                  const [category, action] = perm.split(".")
                  return (
                    <div
                      key={perm}
                      className="flex items-center justify-between rounded-lg border border-border px-3 py-2"
                    >
                      <div className="flex items-center gap-2">
                        <span className="text-xs font-mono text-muted-foreground bg-muted px-1.5 py-0.5 rounded">
                          {category}
                        </span>
                        <span className="text-sm font-medium capitalize">
                          {action}
                        </span>
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
    </div>
  )
}
